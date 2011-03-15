#!/bin/bash
### BEGIN HEADER
db_dir="sys"
db_usr=""

cmd="i"
while getopts "liru:d:" opt; do
  case $opt in
    l)
      cmd="l"
    ;;
    i)
      cmd="i"
    ;;
    r)
      cmd="r"
    ;;
    u)
      db_usr=$OPTARG
    ;;
    d)
      db_dir=$OPTARG
    ;;
    \?)
      echo "Invalid option: -$OPTARG" >&2
      exit 1
    ;;
  esac
done


# check directory
if ! test -d ${db_dir} ; then
  echo "Directory error: ${db_dir}"
  exit 1
fi
# change directory
cd ${db_dir}
# read DB config
db_cfg="./db.cfg"
if ! test -r ${db_cfg} ; then
  echo "DB config error: ${db_cfg}"
  exit 2
fi
. ${db_cfg}

if test "${db_usr}" = "" ; then
  db_usr="u_admin"
fi
### END HEADER


# LOGIN
if test "${cmd}" = "l"; then
  # clean & init db
  echo -e "\nLogin to database: ${PG_DB} @ ${PG_HOST} : ${PG_PORT}\n"
  ${PG_CMD} -U "${db_usr}" -h "${PG_HOST}" -p "${PG_PORT}" -d ${PG_DB}
  exit $?
fi

# COMMON
# create DB init file
timestamp=`date`
echo -e "\n--\n-- ${timestamp}\n--" > "${SQL_TEMP}"


# RESET
if test "${cmd}" = "r"; then
  echo -e "\nDatabase reset: ${PG_DB} @ ${PG_HOST} : ${PG_PORT}"

  for skel in ${SQL_RESET[@]} ; do
    echo -e "\n\n\n-- skel src: ${skel}" >> "${SQL_TEMP}"
    cat "${skel}" >> "${SQL_TEMP}"
  done

  echo -ne "\nReset database? (y/n) "
  read ans
  if test "${ans}" != "y"; then
    exit 3
  fi
  $PG_CMD -f "${SQL_TEMP}" -U "${PG_USER}" -h "${PG_HOST}" -p "${PG_PORT}" -d "${PG_DB}"
  exit $?
fi



# INIT
if test "${cmd}" = "i"; then
  echo -e "\nDatabase initialization on ${db_dir}: ${PG_DB} @ ${PG_HOST} : ${PG_PORT}"
  echo -e "\nWARNING: ALL DATA WILL BE LOST!"

  for init in ${SQL_INIT[@]} ; do
    echo -e "\n\n\n-- init src: ${init}" >> "${SQL_TEMP}"
    cat "${init}" >> "${SQL_TEMP}"
  done

  # read DB skel
  for skel in ${SQL_SKEL[@]} ; do
    echo -e "\n\n\n-- skel src: ${skel}" >> "${SQL_TEMP}"
    cat "${skel}" >> "${SQL_TEMP}"
  done

  # clean before init
  if test "${SQL_CLEAN:-}" != "" ; then
    echo -ne "\n0. Step: Run the cleaner script? (y/n) "
    read ans
    if test "${ans}" != "y"; then
      exit 2
    fi
    $PG_CMD -f "${SQL_CLEAN}" -U "${PG_USER}" -h "${PG_HOST}" -p "${PG_PORT}" -d "${PG_DB}"
  fi

  # init
  echo -ne "\n1. Step: Initialize the database? (y/n) "
  read ans
  if test "${ans}" != "y"; then
    exit 2
  fi
  $PG_CMD -f "${SQL_DBINIT}" -U "${PG_USER}" -h "${PG_HOST}" -p "${PG_PORT}"

  # setup
  echo -ne "\n2. Step: Setup the database? (y/n) "
  read ans
  if test "${ans}" != "y"; then
    exit 3
  fi
  $PG_CMD -f "${SQL_TEMP}" -U "${PG_USER}" -h "${PG_HOST}" -p "${PG_PORT}" -d "${PG_DB}"
  exit $?
fi
