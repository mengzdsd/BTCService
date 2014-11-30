#!/usr/bin/perl
#
# @File checklinedatafiles.pl
# @Author mengz
# @Created Nov 25, 2014 9:23:27 PM
#

use strict;

use XML::DOM;

my $name = "犹梦哲";
my $buffer = $ENV{QUERY_STRING};
#my $buffer = 'line=c58';
# Split information into name/value pairs
my @pairs = split(/=/, $buffer);
#my $lineName = "c58";
my $lineName = $pairs[1];

my $fileDir = "../xml/line/";
my $lineFile = $fileDir . $lineName . '-bus.xml';

my $strRet = "";
if ( -e $lineFile ) {
	my $xmlParser = new XML::DOM::Parser;
	my $xmlDoc = $xmlParser->parsefile($lineFile);
#	print "Parse file success.\n";
	foreach ($xmlDoc->getElementsByTagName('bus')) {
		my $str = '<option>' . $_->getFirstChild()->toString() . '</option>';
		$strRet .= $str;
	}
	$xmlDoc->dispose;
} else {
	$strRet = "NULL";
}

print "Content-type: text/plain\n\n";
print $strRet;