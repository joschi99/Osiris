#!/usr/bin/perl -w
#
# script for nagios notify via Jabber / Google Talk Instant Messaging
#   using XMPP protocol and SASL PLAIN authentication.
#
# author: Andrew Elwell <A.Elwell@physics.gla.ac.uk>
# based on work by Thus0 <Thus0@free.fr> and  David Cox
#
# released under the terms of the GNU General Public License v2
# Copyright 2007 Andrew Elwell.
use strict;
use Net::XMPP;

## Configuration
my $username = "your.google.username";
my $password = "your.google.password";
my $resource = "Osiris 2.0";
## End of configuration

my $len = scalar @ARGV;
if ($len ne 2) {
   die "Usage...\n $0 [jabberid] [message]\n";
}
my @field=split(/,/,$ARGV[0]);
#------------------------------------
# Google Talk & Jabber parameters :
my $hostname = 'talk.google.com';
my $port = 5222;
my $componentname = 'gmail.com';
my $connectiontype = 'tcpip';
my $tls = 1;
#------------------------------------
my $Connection = new Net::XMPP::Client();
# Connect to talk.google.com
my $status = $Connection->Connect(
       hostname => $hostname, port => $port,
       componentname => $componentname,
       connectiontype => $connectiontype, tls => $tls);
if (!(defined($status))) {
   print "ERROR:  XMPP connection failed.\n";
   print "        ($!)\n";
   exit(0);
}
# Change hostname
my $sid = $Connection->{SESSION}->{id};
$Connection->{STREAM}->{SIDS}->{$sid}->{hostname} = $componentname;
# Authenticate
my @result = $Connection->AuthSend(
       username => $username, password => $password,
       resource => $resource);
if ($result[0] ne "ok") {
   print "ERROR: Authorization failed: $result[0] - $result[1]\n";
   exit(0);
}
# Send messages
foreach ( @field ) {
$Connection->MessageSend(
        to       => "$_\@$componentname",
        resource => $resource,
        subject  => "Notification",
        type     => "chat",
        body     => $ARGV[1]);
}
