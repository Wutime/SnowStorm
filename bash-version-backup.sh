#!/bin/bash

# Navigate to the script directory
cd "$(dirname "$0")"

# Variables
ROOT_DIR="$(pwd)"
ADDON_JSON="addon.json"
GIT_IGNORE=".gitignore"
DEV_DIR="./#dev"

# Ensure the #dev directory exists
mkdir -p "$DEV_DIR"

# Check if jq is installed
if ! command -v jq &> /dev/null
then
    echo "jq command not found. Please install jq to continue."
    exit 1
fi

# Read the version from addon.json
VERSION_STRING=$(jq -r '.version_string' "$ADDON_JSON")
if [ -z "$VERSION_STRING" ]
then
    echo "Error reading version_string from $ADDON_JSON."
    exit 1
fi

FILENAME=$(echo "$VERSION_STRING" | tr ' ' '-')
TAR_FILE="$DEV_DIR/$FILENAME.tar.gz"

# Confirm the tar file creation
echo "The filename.tar.gz about to be created is: $TAR_FILE"
read -p "Do you want to continue? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    echo "Operation cancelled."
    exit 1
fi


# Prompt to run xf-addon:sync-json
NAMESPACE=$(basename "$(dirname "$ROOT_DIR")")
ADDON=$(basename "$ROOT_DIR")
CMD="php ../../../../cmd.php xf-addon:sync-json $NAMESPACE/$ADDON"

echo "Do you want to run the following command?"
echo "$CMD"
read -p "(y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]
then
    $CMD
fi

# Prompt to run xf-addon:export
NAMESPACE=$(basename "$(dirname "$ROOT_DIR")")
ADDON=$(basename "$ROOT_DIR")
CMD="php ../../../../cmd.php xf-addon:export $NAMESPACE/$ADDON"

echo "Do you want to run the following command?"
echo "$CMD"
read -p "(y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]
then
    $CMD
fi

# Check if the tar file already exists
if [[ -f "$TAR_FILE" ]]; then
    read -p "$TAR_FILE already exists. Do you want to overwrite it? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]
    then
        echo "Operation cancelled."
        exit 1
    fi
fi

# Construct the tar command with individual --exclude options
EXCLUDE_ARGS=("--exclude='$DEV_DIR'")
if [ -f "$GIT_IGNORE" ]; then
    echo "Reading .gitignore:"
    while IFS= read -r line
    do
        if [[ ! "$line" =~ ^# && ! -z "$line" ]]; then
            EXCLUDE_PATH="./${line%/}"
            EXCLUDE_ARGS+=("--exclude='$EXCLUDE_PATH'")
            echo "Excluding: $EXCLUDE_PATH"
        fi
    done < "$GIT_IGNORE"
fi

# Construct the tar command
TAR_CMD="tar -czf '$TAR_FILE'"
for arg in "${EXCLUDE_ARGS[@]}"; do
    TAR_CMD+=" $arg"
done
TAR_CMD+=" ."

# Print the tar command for debugging
echo -e "\n\nExecuting tar command:\n$TAR_CMD\n\n"

# Execute the tar command
eval $TAR_CMD

# Output filename, size, and number of files included
FILE_SIZE=$(du -sh "$TAR_FILE" | cut -f1)
NUM_FILES=$(tar -tzf "$TAR_FILE" | wc -l)

echo "Created $TAR_FILE"
echo "Size: $FILE_SIZE"
echo "Number of files: $NUM_FILES"

