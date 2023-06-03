#!/bin/bash

# Set variables
FTP_HOST="ftp.u2k.site"
FTP_USERNAME="nczolksu"
FTP_PASSWORD="0RhtXKdeioPj"
REMOTE_DIRECTORY="/home/nczolksu"
GITHUB_REPO="https://github.com/Jerril/u2k-api.git"
GITHUB_BRANCH="main"

# Clone the GitHub repository
echo "Updating project from $GITHUB_REPO ..."

git clone $GITHUB_REPO
cd u2k-api

# Checkout the desired branch
git checkout $GITHUB_BRANCH

# Install dependencies and perform Laravel-specific tasks
echo "installing necessary packages and running migration ..."

composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan optimize

# Connect to the FTP/SFTP server
lftp -c "open -u $FTP_USERNAME,$FTP_PASSWORD $FTP_HOST; set ssl:verify-certificate no; mirror -R . $REMOTE_DIRECTORY"

echo "done."

# Exit the script
exit
