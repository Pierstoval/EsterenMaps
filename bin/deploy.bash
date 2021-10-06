#!/bin/bash

set -e

#     _
#    / \    Disclaimer!
#   / ! \   Please read this before continuing.
#  /_____\  Thanks ☺ ♥
#
# This is the deploy script used in production.
# It does plenty tasks:
#  * Run scripts that are mandatory after a deploy.
#  * Update RELEASE_VERSION and RELEASE_DATE environment vars,
#  * Save the values in env files for CLI and webserver.
#  * Send by email the analyzed changelog (which might not be 100% correct, but it's at least a changelog).

# bin/ directory
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Project directory
cd ${DIR}/../

DIR="$(pwd)"

echo "Working directory: ${DIR}"

CLI_FILE="../env"
NGINX_FILE="../env_nginx.conf"
CHANGELOG_FILE=${DIR}/../_tmp_changelog.txt

LAST_VERSION=$(grep -o -E "RELEASE_VERSION.*[0-9]+.*" ${CLI_FILE} | sed -r 's/[^0-9]+//g')
NEW_VERSION=$(expr ${LAST_VERSION} + 1)
LAST_DATE=$(grep -o -E "RELEASE_DATE=\"?[^\"]+\"?" ${CLI_FILE} | sed -r 's/^.*="?([^"]+)"?$/\1/g')
NEW_DATE=$(date --rfc-3339=seconds)

echo "[DEPLOY] > Current version: ${LAST_VERSION}"
echo "[DEPLOY] > Last build date: ${LAST_DATE}"

echo "[DEPLOY] > Update repository branch"

git fetch --all --prune

CHANGELOG=$(git changelog v${LAST_VERSION}...origin/main | sed 1d)
CHANGELOG_SIZE=$(echo "${CHANGELOG}" | wc -l)
CHANGELOG_SIZE_CHARS=$(echo "${CHANGELOG}" | wc -m)
if [ "${CHANGELOG_SIZE_CHARS}" -lt "1" ]; then
    echo "[DEPLOY] > ${CHANGELOG}"
    echo "[DEPLOY] > No new commit! Terminating..."
    exit 1
else
    echo "[DEPLOY] > Retrieved $((CHANGELOG_SIZE)) commits(s) in changelog:"
    echo "[DEPLOY] > ${CHANGELOG}"
fi

# Just a safety because cross-platform isn't something in NodeJS...
git checkout package-lock.json

echo "[DEPLOY] > Applying these commits..."
git merge origin/main

echo "[DEPLOY] > Done!"

if [[ -f ${CLI_FILE} ]]
then
    echo "[DEPLOY] > Loading env file ${CLI_FILE}"
    source ${CLI_FILE}
fi

echo "[DEPLOY] > Executing scripts..."
echo "[DEPLOY] > "

#
# These scripts are "wrapped" because they might have been updated between deploys.
# Only this "deploy.bash" script can't be updated, because it's executed on deploy.
# But having the scripts executed like this is a nice opportunity to update the scripts between deploys.
#
bash ./bin/deploy_scripts.bash

echo "[DEPLOY] > Done!"
echo "[DEPLOY] > Now updating environment vars..."
echo "[DEPLOY] > New version: ${NEW_VERSION}"
echo "[DEPLOY] > New build date: ${NEW_DATE}"

sed -i -e "s/RELEASE_VERSION=.*/RELEASE_VERSION=\"v${NEW_VERSION}\"/g" ${CLI_FILE}
sed -i -e "s/RELEASE_VERSION .*/RELEASE_VERSION \"v${NEW_VERSION}\";/g" ${NGINX_FILE}

sed -i -e "s/RELEASE_DATE=.*/RELEASE_DATE=\"${NEW_DATE}\"/g" ${CLI_FILE}
sed -i -e "s/RELEASE_DATE .*/RELEASE_DATE \"${NEW_DATE}\";/g" ${NGINX_FILE}

echo "[DEPLOY] > Restart web server..."
sudo service nginx reload
echo "[DEPLOY] > Done!"

echo "[DEPLOY] > Now generating changelogs..."

echo "" > ${CHANGELOG_FILE}

echo "New version: v${NEW_VERSION}"    >> ${CHANGELOG_FILE}
echo "Released on: ${NEW_DATE}"        >> ${CHANGELOG_FILE}
echo ""                                >> ${CHANGELOG_FILE}
echo "List of all changes/commits:"    >> ${CHANGELOG_FILE}
echo "${CHANGELOG}"                    >> ${CHANGELOG_FILE}
echo ""                                >> ${CHANGELOG_FILE}
echo "Reminder of all portals:"        >> ${CHANGELOG_FILE}
echo ""                                >> ${CHANGELOG_FILE}
echo "* https://www.studio-agate.com"  >> ${CHANGELOG_FILE}
echo "* https://www.vermine2047.com"   >> ${CHANGELOG_FILE}
echo "* https://www.dragons-rpg.com"   >> ${CHANGELOG_FILE}
echo "* https://fateforge.org/en"      >> ${CHANGELOG_FILE}
echo "* https://portal.esteren.org"    >> ${CHANGELOG_FILE}
echo "* https://maps.esteren.org"      >> ${CHANGELOG_FILE}

echo "[DEPLOY] > FULL CHANGELOG"
cat ${CHANGELOG_FILE}

if [[ -f "${DIR}/../post_deploy.bash" ]]
then
    echo "[DEPLOY] > Executing post-deploy scripts"
    bash ../post_deploy.bash ${NEW_VERSION} ${CHANGELOG_FILE}
fi

echo "[DEPLOY] > Tagging release..."
echo "[DEPLOY] > Pushing it to Git..."

git tag -s -F ${CHANGELOG_FILE} "v${NEW_VERSION}"
git push origin "v${NEW_VERSION}"

rm ${CHANGELOG_FILE}

echo "[DEPLOY] > Done!"
echo "[DEPLOY] > Deploy finished!"
