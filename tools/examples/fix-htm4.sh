# Script to partially upgrade the wikitext to HTML4

# Regex to be called n times where n is the number of attributes to be fixed + 1 (bgcolor, color, valign, align + 1 = 5)
PATTERN_MERGE_STYLES='/style="([a-zA-Z0-9-; #:%]+?);?"((?: +[a-zA-Z]+ *= *("?)[a-zA-Z0-9-; #:%]+\3)*) style="([a-zA-Z0-9-; #:%]+?);?"/'
REPLACE_MERGE_STYLES='style="$1; $4"$2'

# ___ ____________________________________________________________________________
../replace.php                                                                    \
--wiki=itwiki                                                                      \
--generator=search                                                                  \
--gsrsearch='insource:bgcolor'                                                       \
--regex                                                                               \
--summary="Bot: migrazione attributi deprecati da [[HTML 4.0]]"                        \
                                                                                        \
'/(.{0,10})[bB]gcolor *= *["”]?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})+["”]?(.{0,10})/'          \
'$1style="background:#$2"$3'                                                              \
                                                                                           \
'/(.{0,10})color *= *["”]?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})+["”]?(.{0,10})/'                  \
'$1style="color:#$2"$3'                                                                      \
                                                                                              \
'/(.{0,10})[bB]gcolor *= *["”]?(#[a-z0-9A-Z]{6}|#[a-zA-Z0-9]{3}|[a-zA-Z]{3,})+["”]?(.{0,10})/' \
'$1style="background:$2"$3'                                                                     \
                                                                                                 \
'/(.{0,10})[cC]olor *= *["”]?(#[a-z0-9A-Z]{6}|#[a-zA-Z0-9]{3}|[a-zA-Z]{3,})+["”]?(.{0,10})/'      \
'$1style="color:$2"$3'                                    \
                                                          \
'/#([a-fA-F0-9])\1([a-fA-F0-9])\2([a-fA-F0-9])\3(;?)"/'   \
'#$1$2$3$4"'                                              \
                                                          \
'/(.{0,10})valign *= *["”]?([a-zA-Z0-9-])["”]?(.{0,10})/' \
'$1style="vertical-align:$2"$3'                           \
                                                          \
'/(.{0,10})align *= *["”]?([a-zA-Z0-9-]+)["”]?(.{0,10})/' \
'$1style="text-align:$2"$3'                               \
                                                          \
$PATTERN_MERGE_STYLES $REPLACE_MERGE_STYLES               \
$PATTERN_MERGE_STYLES $REPLACE_MERGE_STYLES               \
$PATTERN_MERGE_STYLES $REPLACE_MERGE_STYLES               \
$PATTERN_MERGE_STYLES $REPLACE_MERGE_STYLES               \
$PATTERN_MERGE_STYLES $REPLACE_MERGE_STYLES               \
