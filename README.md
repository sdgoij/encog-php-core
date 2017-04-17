# Introduction

[Encog](http://www.heatonresearch.com/encog/) is a machine learning framework developed by [Jeff Heaton](http://www.heatonresearch.com/about/) for Java and C#. This is a PHP7 port of the Java version.

This is an experiment, to find out if PHP7 is ready for machine learning tasks. The short answer is not really. But with some extra effort I do believe it can work really well. By (re)writing some of the components (ActivationFunctions, Propagation, GradientWorker, ..) in C as PHP extension it should be able to perform just fine.

Another issue is parallelism, the original Java code is designed to work multi-threaded but this is not supported by PHP out of the box. It can be done, by using [Joe Watkins](https://github.com/krakjoe)'s excellent [pthreads](https://github.com/krakjoe/pthreads) extension but this requires a thread safe PHP built, which most are not (for a good reason). Needs more research, for now park this issue.

## TODO

 - Finish this readme
 - Debug neural\\{neat,som} packages
 - A lot, see [encog-java-core](https://github.com/encog/encog-java-core)

## Note

This is still a work in progress, please do not use in production.
