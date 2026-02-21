# Command Execution Rules

Before executing a command in the terminal, always MUST use `cd` to change to the directory at the top of the source project using an absolute path beginning with /cygdrive/c/ on Windows or / otherwise. Commands that accept arguments with relative file paths within the project should use paths relative to the directory at the top of the source project. This is not optional.
