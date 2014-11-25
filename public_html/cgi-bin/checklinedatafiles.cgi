#!/usr/bin/perl
#
# @File checklinedatafiles.pl
# @Author mengz
# @Created Nov 25, 2014 9:23:27 PM
#

use strict;

use XML::Simple;
#use Data::Dumper;

my $buffer = $ENV{QUERY_STRING};
#$ENV{'REQUEST_METHOD'} =~ tr/a-z/A-Z/;
#if ($ENV{'REQUEST_METHOD'} eq "GET")
#{
#   $buffer = $ENV{'QUERY_STRING'};
#}
# Split information into name/value pairs
my @pairs = split(/&/, $buffer);

my $date = $pairs[0];
my $bus = $pairs[1];
my $numberOfFiles;
my $dataDir = "../linedata/";
my @fileList = ();

#$dateArg = $ENV{QUERY_STRING};
#my $date = "20141121";

if(opendir DIR, "$dataDir/$date/") {
	@fileList = grep /\.xml$/,readdir DIR;
	closedir DIR;
	$numberOfFiles = @fileList;
} else {
	$numberOfFiles = 0;
}

my $xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>';

if ($numberOfFiles != 0) {
    my $xml = new XML::Simple;
    my $xmldata;
    my $xmlStr = "<filelist number=\"$numberOfFiles\">";
    foreach (@fileList) {
        $xmldata = $xml->XMLin("$dataDir/$date/$_");
        my $lineId = $xmldata->{Id};
        my $status;
        my $checker;
        if ($xmldata->{Checker} != undef) {
            $status = "true";
            $checker = $xmldata->{Checker}->{Name};
        } else {
            $status = "false";
            $checker = "NULL";
        }
#        print "$lineId : $status : $checker\n";
        $xmlStr .= "<file><name>$lineId</name><status>$status</status><checker>$checker</checker></file>"
#        $xmlStr .= "<file>$lineId-$status-$checker</file>";
    }
    $xmlStr .= "</filelist>";
    $xmlStr = $xmlHeader . $xmlStr;
    print "Content-type: text/xml\n\n";
    print $xmlStr;
} else {
    my $xmlStr = $xmlHeader . "<filelist number=\"0\"></filelist>";
    print "Content-type: text/xml\n\n";
    print $xmlStr;
}