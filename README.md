# CloudBuster plugin for ExpressionEngine 2.4.x and 2.5.x

Busts caching on Cloudflare when entries or files are updated/uploaded.

![CloudBuster Icon](https://rawgit.com/bluestorm/ee2-cloudbuster/master/resources/icon.svg)

## Installation

To install CloudBuster, follow these steps:

1. Download & unzip the file and place the `cloudbuster` directory into your `third_party` directory.
2.  -OR- do a `git clone git@github.com:bluestorm/cloudbuster.git cloudbuster` directly into your `third_party` folder.  You can then update it with `git pull`.
4. Install extension in the EE Control Panel under Add-Ons > Extensions.
5. The extension folder should be named `cloudbuster` for EE to see it.

CloudBuster works on EE 2.4.x.

## Configuring CloudBuster

Click the Settings link next to CloudBuster under Add-Ons > Extensions in the EE Control Panel and enter your Cloudflare API key, email and Zone ID.

## Using CloudBuster

You don't need to do a thing! The extension automatically flushes entry and file urls from Cloudflare when they're created, updated or deleted.