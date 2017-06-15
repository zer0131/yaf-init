#!/bin/sh

# 部署代码需要进入当前目录执行该脚本

PRODUCT_NAME="yf"
APP_NAME="hello"
APP_PATH=$(dirname "$PWD")
ROOT_PATH=$(dirname "$APP_PATH")

mkdir -p $ROOT_PATH/public/$APP_NAME
#mkdir -p $ROOT_PATH/lib/$PRODUCT_NAME/api/$APP_NAME

cp index.php  $ROOT_PATH/public/$APP_NAME/
#cp -r api/* $ROOT_PATH/lib/$PRODUCT_NAME/api/$APP_NAME/