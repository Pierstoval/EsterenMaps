StudioAgate portal Dockerfile
=============================

See details on the [Docker hub](https://hub.docker.com/r/pierstoval/studio-agate-portal/)

# Details

This image is **never built at runtime**.

Its build takes a long time.

That's why we build it from time to time, and push it to the Docker hub.

Development environment and CI will use this docker image, because downloading it is way faster than building it.

So remember: if you update something in the image, you must push it.

# Build the image

Run `make build`.

# Push it

Run `make push`. Be sure you have access to the Docker Hub, sometimes a `docker login` is necessary.
