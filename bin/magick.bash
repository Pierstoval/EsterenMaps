#!/bin/bash

command=$1
shift

if [[ $command == *"-version"* ]]; then
    /usr/bin/convert -version
    exit $?
fi

if [[ $command != "animate" && $command != "compare" && $command != "composite" && $command != "conjure" && $command != "convert" && $command != "display" && $command != "identify"&& $command != "import" && $command != "mogrify" && $command != "montage" && $command != "stream" ]]; then
    echo "Command "$command" does not exist."
    exit 1
fi

/usr/bin/"$command" ${@}
