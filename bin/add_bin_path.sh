#!/bin/bash

echo "export PATH=\$PATH:$(cd $(dirname $0) && pwd)" >> ~/.bash_profile
