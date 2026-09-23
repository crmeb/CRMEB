#!/usr/bin/env python3
import argparse
import os
from pathlib import Path
import re
from subprocess import list2cmdline, run
from tempfile import NamedTemporaryFile

CLANG_FORMAT_VERSION = '18.1.6'

INCLUDE_REGEX = re.compile(r'^ext/.*\.(c|h|inl)$')
EXCLUDE_REGEX = re.compile(r'^$')

arg_parser = argparse.ArgumentParser(description="Check with clang-format")
arg_parser.add_argument('-i', '--inplace-edit', action='store_true',
                        help="Edit files inplace")
args = arg_parser.parse_args()

os.chdir(Path(__file__).parent)

# create file containing list of all files to format
filepaths_file = NamedTemporaryFile(delete=False)
for dirpath, dirnames, filenames in os.walk('.'):
    for filename in filenames:
        # our regexes expect filepath to use forward slash
        filepath = Path(dirpath, filename).as_posix()
        if not INCLUDE_REGEX.match(filepath):
            continue
        if EXCLUDE_REGEX.match(filepath):
            continue

        filepaths_file.write(f"{filepath}\n".encode())
filepaths_file.close()

# use pipx to run clang-format from PyPI
# this is a simple way to run the same clang-format version regardless of OS
cmd = ['pipx', 'run', f'clang-format=={CLANG_FORMAT_VERSION}',
       f'--files={filepaths_file.name}']
if args.inplace_edit:
    cmd += ['-i']
else:
    cmd += ['--Werror', '--dry-run']

print(f"{Path.cwd()}$ {list2cmdline(cmd)}")
if run(cmd).returncode:
    exit(1)
