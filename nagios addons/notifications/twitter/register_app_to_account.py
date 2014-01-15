#!/usr/bin/env python
 
import tweepy
 
CONSUMER_KEY = 'iEgK2m7v6SUHqRiDvyzXBQ'
CONSUMER_SECRET = 'U6O9dDDhLx4FgKktdYx8iepVUmhYUlSEgyw59H914'
 
auth = tweepy.OAuthHandler(CONSUMER_KEY, CONSUMER_SECRET)
auth_url = auth.get_authorization_url()
print 'Please authorize: ' + auth_url
verifier = raw_input('PIN: ').strip()
auth.get_access_token(verifier)
print "ACCESS_KEY = '%s'" % auth.access_token.key
print "ACCESS_SECRET = '%s'" % auth.access_token.secret
