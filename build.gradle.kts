plugins.withId("org.jetbrains.kotlin.kapt") {
    tasks.withType<JavaCompile>().configureEach {
        options.compilerArgs.add("--add-exports=jdk.compiler/com.sun.tools.javac.main=ALL-UNNAMED")
    }
}


plugins {
    alias(libs.plugins.android.application) apply false
    alias(libs.plugins.android.library) apply false
    alias(libs.plugins.kotlin.android) apply false
    alias(libs.plugins.kotlin.kapt) apply false
}
