#!/bin/bash
set -e

# === CONFIG ===
JDK_TARBALL_URL="https://download.oracle.com/java/17/archive/jdk-17.0.12_linux-x64_bin.tar.gz"
JDK_INSTALL_DIR="/opt/java"
JDK_VERSION="jdk-17.0.12"
ANDROID_SDK_TOOLS_ZIP="https://dl.google.com/android/repository/commandlinetools-linux-9477386_latest.zip"
ANDROID_HOME="${ANDROID_HOME:-$HOME/android-sdk}"
PROJECT_ROOT="${PROJECT_ROOT:-$PWD}"

# === JAVA SETUP ===
echo "==> Checking for existing Java installation..."
if command -v java >/dev/null 2>&1; then
  JAVA_VERSION=$(java -version 2>&1 | awk -F '"' '/version/ {print $2}')
  JAVA_MAJOR=$(echo "$JAVA_VERSION" | awk -F. '{print ($1 == "1") ? $2 : $1}')
  echo "✅ Java detected: version $JAVA_VERSION"

  if (( JAVA_MAJOR >= 17 )); then
    echo "✅ Java version is 17 or higher. Skipping install."
    JAVA_BIN="$(readlink -f "$(command -v java)")"
    JAVA_HOME="$(dirname "$(dirname "$JAVA_BIN")")"
    export JAVA_HOME
    export PATH="$JAVA_HOME/bin:$PATH"
  else
    echo "⚠️ Java version is below 17. Proceeding with upgrade..."
  fi
fi

if [[ -z "$JAVA_HOME" || ! -x "$JAVA_HOME/bin/java" ]]; then
  echo "==> Installing Oracle JDK 17.0.12..."
  mkdir -p "$JDK_INSTALL_DIR"
  wget -O /tmp/jdk17.tar.gz "$JDK_TARBALL_URL"
  tar -xzf /tmp/jdk17.tar.gz -C "$JDK_INSTALL_DIR"
  JAVA_HOME="$JDK_INSTALL_DIR/$JDK_VERSION"
  export JAVA_HOME
  export PATH="$JAVA_HOME/bin:$PATH"

  echo "==> Configuring update-alternatives..."
  update-alternatives --install /usr/bin/java  java  "$JAVA_HOME/bin/java" 17120
  update-alternatives --install /usr/bin/javac javac "$JAVA_HOME/bin/javac" 17120
  update-alternatives --set java  "$JAVA_HOME/bin/java"
  update-alternatives --set javac "$JAVA_HOME/bin/javac"

  echo "==> Persisting JAVA_HOME..."
  echo "export JAVA_HOME=$JAVA_HOME" | tee /etc/profile.d/jdk17.sh
  echo 'export PATH=$JAVA_HOME/bin:$PATH' | tee -a /etc/profile.d/jdk17.sh
  source /etc/profile.d/jdk17.sh
fi

echo "✅ Java setup complete. JAVA_HOME is set to $JAVA_HOME"
java -version

# === ANDROID SDK SETUP ===
echo "==> Installing Android command-line tools..."
mkdir -p "$ANDROID_HOME/cmdline-tools"
cd "$ANDROID_HOME/cmdline-tools"

if [[ ! -f commandlinetools.zip ]]; then
  wget -O commandlinetools.zip "$ANDROID_SDK_TOOLS_ZIP"
fi

if [[ ! -d latest ]]; then
  unzip -o commandlinetools.zip
  mv -f cmdline-tools latest
fi

export ANDROID_HOME
export PATH="$ANDROID_HOME/cmdline-tools/latest/bin:$PATH"

echo "==> Accepting Android SDK licenses..."
yes | sdkmanager --licenses || true

echo "==> Installing platform tools and build tools..."
yes | sdkmanager "platform-tools" "platforms;android-34" "build-tools;34.0.0" || true

# === BUILD PROJECT ===
echo "==> Building Android project..."
cd "$PROJECT_ROOT"
chmod +x ./gradlew
./gradlew --stop
./gradlew clean
rm -rf ~/.gradle/caches/
./gradlew assembleRelease --stacktrace --no-daemon

echo "✅ Build complete."
echo "APK(s): $PROJECT_ROOT/app/build/outputs/apk/release/"
