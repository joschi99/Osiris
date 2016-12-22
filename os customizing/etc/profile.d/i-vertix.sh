sw=/opt/bi-s/software
plugins=/usr/lib/nagios/plugins
centplugins=/usr/lib/centreon/plugins
centlog=/var/log/centreon-engine/centengine.log
backup=/opt/bi-s/cifs/backup
syslog=/opt/bi-s/cifs/rsyslog
tools=/opt/bi-s/cifs/tools

alias sw="cd $sw"
alias plugins="cd $plugins"
alias centplugins="cd $centplugins"
alias centlog="tail -f $centlog"
alias backup="cd $backup"
alias syslog="cd $syslog"
alias tools="cd $tools"
alias stirb="shutdown -h now"