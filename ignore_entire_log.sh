#!/usr/bin/env bash

LOG_FILE=data/log.txt

grep "|post|" ${LOG_FILE}
grep "|comment|" ${LOG_FILE}
grep "|nested_comment|" ${LOG_FILE}
grep "|photo_comment|" ${LOG_FILE}
grep "|video_comment|" ${LOG_FILE}
