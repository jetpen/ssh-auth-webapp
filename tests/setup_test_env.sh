#!/bin/bash

# Test environment setup script
# This script sets up the test environment before test execution

set -e

# Dynamically calculate the project home directory (parent of script directory)
PROJECT_HOME="$(cd "$(dirname "$(dirname "$0")")" && pwd)"

echo "Setting up test environment..."
echo "Project home: ${PROJECT_HOME}"

# Set up test context configuration
if [ ! -f "${PROJECT_HOME}/tests/test_context.sh" ]; then
    if [ -f "${PROJECT_HOME}/tests/test_context.sh.template" ]; then
        cp "${PROJECT_HOME}/tests/test_context.sh.template" "${PROJECT_HOME}/tests/test_context.sh"
        echo "ERROR: Created ${PROJECT_HOME}/tests/test_context.sh from template"
        echo "Please edit ${PROJECT_HOME}/tests/test_context.sh with proper test context before running tests."
        exit 1
    else
        echo "ERROR: ${PROJECT_HOME}/tests/test_context.sh.template not found. Cannot set up test context."
        exit 1
    fi
fi

# Source the test context configuration
source "${PROJECT_HOME}/tests/test_context.sh"
echo "Test context configuration loaded from ${PROJECT_HOME}/tests/test_context.sh"

# Create remote directory on Web server
echo "Creating remote directory ${WEB_SERVER_TARGET_DIR} on ${WEB_SERVER_HOST}..."
ssh -i ${SRE_SSH_KEY} "${SRE_USER}@${WEB_SERVER_HOST}" "mkdir -p ${WEB_SERVER_TARGET_DIR}"

# Copy src files to remote Web server
echo "Copying src files to ${WEB_SERVER_HOST}:${WEB_SERVER_TARGET_DIR}/..."
scp -i ${SRE_SSH_KEY} -r "${PROJECT_HOME}/src/"* "${SRE_USER}@${WEB_SERVER_HOST}:${WEB_SERVER_TARGET_DIR}/"

echo "Remote setup complete."

echo "Test environment setup complete."
