#!/bin/bash
#/// \file php.cli.sh
#/// \brief php shell wrapper

### begin header
if test "${COBRA_BOOTSTRAP}" == "" ; then
  export COBRA_BOOTSTRAP="${PWD}/../bs.cli.php"
fi
cobra_bootstrap=${COBRA_BOOTSTRAP}
cobra_home=`dirname ${cobra_bootstrap}`
phpcli_kernel="./k.sh"
if ! test -r ${phpcli_kernel}; then
  echo " Kernel error: ${phpcli_kernel}"
  exit 1
fi
. ${phpcli_kernel}
### end header

script=$(basename $0)
name="${script%%.sh}"
php_script="${phpcli_dir}/${name}.cli.php"
lock="${name}.lck"

create_lock ${lock}
if test $? -eq 0; then
  echo "LOCK"
  exit 1
fi

if ! test -x ${php_script}; then
  echo "${php_script} is not executable"
  delete_lock ${lock}
  exit 2
fi

${php_script}
_r=$?

delete_lock ${lock}

exit ${_r}
