#!/usr/bin/env python
 
import sys
import tweepy
 
CONSUMER_KEY = 'iEgK2m7v6SUHqRiDvyzXBQ'
CONSUMER_SECRET = 'U6O9dDDhLx4FgKktdYx8iepVUmhYUlSEgyw59H914'
ACCESS_KEY = '104955994-DTSYygUfK0lMpuZayNaHW2Xq1iwjJCiWDx33zgd2'
ACCESS_SECRET = 'MoZYMZj9LqxseUIAgCgZ5LJw56pCUeEJnI82A8vUqU'
 
auth = tweepy.OAuthHandler(CONSUMER_KEY, CONSUMER_SECRET)
auth.set_access_token(ACCESS_KEY, ACCESS_SECRET)
api = tweepy.API(auth)
api.update_status(sys.argv[1])
