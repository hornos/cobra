phpcli_dir="${cobra_home}/cli"
shell_dir="${cobra_home}/cli"
lock_dir="${cobra_home}/lock"
log_dir="${cobra_home}/log"


function is_lock() {
  lock="${1}"
  if test -z "${lock}"; then
    return 0
  fi

  lockfile="${lock_dir}/${lock}"

  if test -r "${lockfile}"; then
    return 1
  fi
  return 0
}


function create_lock() {
  lock="${1}"
  if test -z "${lock}"; then
    return 0
  fi

  is_lock "${lock}"
  if test $? -eq 1; then
    return 0
  fi

  now=`date +"%Y-%m-%d[%H:%M:%S]"`
  lockfile="${lock_dir}/${lock}"
  echo "$$" > "${lockfile}"
  return 1
}


function get_lock() {
  lock="${1}"
  if test -z "${lock}"; then
    return 0
  fi

  is_lock "${lock}"
  if test $? -eq 0; then
    return 0
  fi
  lockfile="${lock_dir}/${lock}"
  cat ${lockfile}
  return 1
}


function print_in_lock() {
  lock="${1}"
  msg="${2}"

  if test -z "${lock}"; then
    return 1
  fi

  if test -z "${msg}"; then
    return 1
  fi

  lockfile="${lock_dir}/${lock}"

  is_lock "${lock}"
  if test $? -eq 1; then
    echo "${msg}" >> ${lockfile}
  fi

  return 0
}


function delete_lock() {
  lock="${1}"
  if test -z "${lock}"; then
    return 0
  fi

  lockfile="${lock_dir}/${lock}"
  is_lock "${lock}"
  if test $? -eq 1; then
    rm -f "${lockfile}"
    return $?
  fi
  return 0
}


function kill_lock() {
  lock="${1}"
  if test -z "${lock}"; then
    return 0
  fi

  lockfile="${lock_dir}/${lock}"
  is_lock "${lock}"
  if test $? -eq 1; then
    pid=`cat ${lockfile}`
    kill ${pid}
    return $?
  fi

  return 0
}
