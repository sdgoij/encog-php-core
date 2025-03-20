# Warning

**This package is not suitable for production use. It's currently unmaintained and is published for educational purposes only.**

# Introduction

[Encog](http://www.heatonresearch.com/encog/) is a machine learning framework for Java and C#, developed by [Jeff Heaton](http://www.heatonresearch.com/about/). This is a PHP7 port of the Java version.

This is an experiment, to find out if PHP(7) is ready for machine learning tasks. The short answer is not really. But with some extra effort I do believe it can work really well. By (re)writing some of the components (ActivationFunctions, Propagation, GradientWorker, ..) in C as PHP extension it should be able to perform just fine. It might perform just fine but I'm not sure it's worth the effort. Using the Java or C# tools to train and export the network, to be consumed here might just be a more productive approach. Evaluating simple pre-trained networks works pretty well, see [examples/mnist-draw].

Another issue is parallelism, the original Java code is designed to work multi-threaded but this is not supported by PHP out of the box. It can be done, by using [Joe Watkins](https://github.com/krakjoe)'s excellent [pthreads](https://github.com/krakjoe/pthreads) extension but this requires a thread safe PHP built, which most are not (for a good reason). Needs more research, for now park this issue.
## Usage

### Clone repository and run tests
```shell
git clone https://bitbucket.org/sdgoij/encog-php-core.git
cd encog-php-core
composer install
./vendor/bin/phpuint .
```

### Run the MNIST handwritten digit example
```shell
cd examples/mnist-draw
php -S localhost:8990 example.php 
```

## TODO

 - Finish this readme, or don't
 - A lot, see [encog-java-core](https://github.com/encog/encog-java-core)
 - Improve test coverage and verify test against encog-java-core
 - Make tests run using PHP 8+ and PHPUnit 12+

## Note

This is [still?] a work in progress, please do not use in production. Unless you know what ur doing.
