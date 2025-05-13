#!/bin/bash

echo "🔧 Installing image optimization tools. . ."
sudo apt-get update

sudo apt-get install -y \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    webp \
    libavif-bin

sudo apt-get autoremove

echo "🔧 Installing npm svg optimization tools. . ."
sudo npm install -g svgo




echo "✅ All image optimization tools installed successfully."
