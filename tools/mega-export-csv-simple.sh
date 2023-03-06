#!/bin/sh
# boz-mw - Another MediaWiki API framework
# Copyright (C) 2019-2023 Valerio Bozzolan, contributors
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.

#############################################################
# Mega Export CSV (simple version)
#
# Export the full history of a MediaWiki page in CSV format.
# This is a simple wrapper for the mega-export-csv.php tool.
#############################################################

# main arguments
wiki="$1"
title="$2"
filename="$3"

#
# Function that shows how to use this script
#
message_usage() {
	echo "USAGE"
	echo "  $0 WIKI PAGE_TITLE"
	echo
	echo "EXAMPLE USAGE"
	echo "  $0 itwiki 'Software libero'"
}

#
# Function that shows an help message
#
message_help() {
	echo " _____________________________________"
	echo "/ Welcome in the Mega Wiki Export CSV \\"
	echo "\ (simple version) by boz-php         /"
	echo " -------------------------------------"
	echo "\                             .       ."
	echo " \                           /\`.   .' '"
	echo "  \                  .---.  <    > <    >  .---."
	echo "   \                 |    \  \ - ~ ~ - /  /    |"
	echo "         _____          ..-~             ~-..-~"
	echo "        |     |   \~~~\.'                    \./~~~/"
	echo "       ---------   \__/                        \__/"
	echo "      .'  O    \     /               /       \  '"
	echo "     (_____,   \`._.'               |         }  \/~~~/"
	echo "     \`----.          /       }     |        /    \__/"
	echo "            \`-.      |       /      |       /      \`. ,~~|"
	echo "                ~-.__|      /_ - ~ ^|      /- _      \`..-'"
	echo "                     |     /        |     /     ~-.     \`-. _  _  _"
	echo "                     |_____|        |_____|         ~ - . _ _ _ _ _>"
	echo
	message_usage
}

#
# Function that shows your mistake in a visible way
#
message_error() {
	echo
	echo "ERROR"
	echo "  $1"
	echo
}

# no wiki no party
if [ -z "$wiki" ]; then
	message_help
	message_error "Please specify a wiki as first argument."
	exit 1
fi

# no title no party
if [ -z "$title" ]; then
	message_help
	message_error "Please specify the article title as second argument."
	exit 1
fi

# assume a nice default for your filename
if [ -z "$filename" ]; then
	file="$wiki-$title.csv"
	echo "You have not expressed a filename. Default:"
	echo "  $file"
fi

# find the current directory - so to understand where we are
# https://unix.stackexchange.com/a/17802/85666
my_directory="$(dirname "$(readlink -f "$0")")"

# execute the tool
${my_directory}/mega-export-csv.php --wiki="$wiki" --file="$wiki-$title.csv" "$title"
