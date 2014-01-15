#Installation
##Tweepy
1. unzip tweepy-2.1-py2.6.zip
2. move the 4 files (api.py, auth.py, binder.py, streaming.py) from directory "SSL workaround" to tweepy-master/tweepy
3. cd tweepy-master
4. python setup.py install

##Register app to Twitter account
1. Read http://talkfast.org/2010/05/31/twitter-from-the-command-line-in-python-using-oauth/
2. ./register_app_to_account.py
3. insert the ACCESS_KEY and ACCESS_SECRET in twitter_commandline.py

#Configuration
1. Copy twitter_commandline.py to Nagios Plugins directory
2. Configure notification commands for host und service in Centreon