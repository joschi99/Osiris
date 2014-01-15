#Installation
##Tweepy
    unzip tweepy-2.1-py2.6.zip

move the 4 files (api.py, auth.py, binder.py, streaming.py) from directory "SSL workaround" to tweepy-master/tweepy

    cd tweepy-master
    python setup.py install

##Register app to Twitter account
Read http://talkfast.org/2010/05/31/twitter-from-the-command-line-in-python-using-oauth/

    ./register_app_to_account.py

follow the instruction and insert the ACCESS_KEY and ACCESS_SECRET in twitter_commandline.py

#Configuration
1. Copy twitter_commandline.py to Nagios Plugins directory
2. Configure notification commands for host und service in Centreon