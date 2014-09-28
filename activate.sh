# Before executing any of the other scripts, run:
#   `source activate.sh`

t1=$(dirname "${0}")
t2=$(pwd -L)
BASEDIR=$(realpath "${t2}/${t1}")

###############################################################################
#find $BASEDIR -type d -name "bin" -print | awk '{print "T" $0}'
PATHEXT=$BASEDIR/vendor/bin:$BASEDIR/bin
#PATHEXT=$(find $BASEDIR -type d -name "bin" -print | tr "\\n" ":")

export OLDPATH=$PATH
export PATH=$PATH:$PATHEXT
