@file:Suppress("UnstableApiUsage")

import org.jetbrains.kotlin.gradle.dsl.JvmTarget
import org.jetbrains.kotlin.gradle.tasks.KotlinCompile

val pkg: String = providers.gradleProperty("amneziawgPackageName").get()

plugins {
    alias(libs.plugins.android.application)
    alias(libs.plugins.kotlin.android)
    alias(libs.plugins.kotlin.kapt) // or use id("org.jetbrains.kotlin.kapt")
}

kapt {
    keepJavacAnnotationProcessors = true
}

kapt {
    javacOptions {
        option("--add-exports", "jdk.compiler/com.sun.tools.javac.main=ALL-UNNAMED")
    }
}


android {
    sourceSets {
        getByName("main").java.srcDirs("src/main/java")
        getByName("release").java.srcDirs("src/release/java")
    }
    buildFeatures {
        buildConfig = true
        dataBinding = true
        viewBinding = true
    }
    namespace = pkg
    defaultConfig {
        applicationId = pkg
        targetSdk = 34
        versionCode = providers.gradleProperty("amneziawgVersionCode").get().toInt()
        versionName = providers.gradleProperty("amneziawgVersionName").get()
        buildConfigField("int", "MIN_SDK_VERSION", minSdk.toString())
    }
    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
        isCoreLibraryDesugaringEnabled = true
    }
    buildTypes {
        release {
            isMinifyEnabled = true
            isShrinkResources = true
            proguardFiles("proguard-android-optimize.txt")
            packaging {
                resources {
                    excludes += "DebugProbesKt.bin"
                    excludes += "kotlin-tooling-metadata.json"
                    excludes += "META-INF/*.version"
                }
            }
        }
        debug {
            applicationIdSuffix = ".debug"
            versionNameSuffix = "-debug"
        }
        create("googleplay") {
            initWith(getByName("release"))
            matchingFallbacks += "release"
        }
    }
    androidResources {
        generateLocaleConfig = true
    }
    lint {
        disable += "LongLogTag"
        warning += "MissingTranslation"
        warning += "ImpliedQuantity"
    }
}

dependencies {
    implementation(project(":tunnel"))
    implementation(libs.androidx.activity.ktx)
    implementation(libs.androidx.annotation)
    implementation(libs.androidx.appcompat)
    implementation(libs.androidx.constraintlayout)
    implementation(libs.androidx.coordinatorlayout)
    implementation(libs.androidx.biometric)
    implementation(libs.androidx.core.ktx)
    implementation(libs.androidx.fragment.ktx)
    implementation(libs.androidx.preference.ktx)
    implementation(libs.androidx.lifecycle.runtime.ktx)
    implementation(libs.androidx.datastore.preferences)
    implementation(libs.google.material)
    implementation(libs.zxing.android.embedded)
    implementation(libs.kotlinx.coroutines.android)
    coreLibraryDesugaring(libs.desugarJdkLibs)
    compileOnly("org.projectlombok:lombok:1.18.32")
    annotationProcessor("org.projectlombok:lombok:1.18.32")
    kapt("org.projectlombok:lombok:1.18.32")
    kapt("com.squareup.moshi:moshi-kotlin-codegen:1.15.0") // example
}

tasks.withType<JavaCompile>().configureEach {
    options.compilerArgs.add("-Xlint:unchecked")
    options.isDeprecation = true
}

tasks.withType<KotlinCompile>().configureEach {
    compilerOptions.jvmTarget.set(JvmTarget.JVM_17)
}
