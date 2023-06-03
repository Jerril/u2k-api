#!/bin/bash

source .env

# Set variables
FTP_HOST=$FTP_HOST
FTP_USERNAME=$FTP_USERNAME
FTP_PASSWORD=$FTP_PASSWORD
REMOTE_DIRECTORY=$REMOTE_DIRECTORY
GITHUB_REPO=$GITHUB_REPO
GITHUB_BRANCH=$GITHUB_BRANCH

# Clone the GitHub repository
echo "Updating project from $GITHUB_REPO ..."

git pull orign $GITHUB_BRANCH
# cd u2k-api


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
