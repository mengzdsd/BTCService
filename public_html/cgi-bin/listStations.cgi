#!/usr/bin/perl
#
# @File listStations.pl
# @Author mengz
# @Created Nov 26, 2014 6:34:16 AM
#

use strict;
use XML::DOM;

my $line = $ENV{QUERY_STRING};
#my $line = "linestations";
my $fileDir = "../xml";
my $xmlfile = "$fileDir/$line.xml";
my $xmlParser = new XML::DOM::Parser;
my $xmldoc = $xmlParser->parsefile($xmlfile);
#print Dumper($xmldata); 
my $stationNameNodes = $xmldoc->getElementsByTagName("name");
my $n = $stationNameNodes->getLength;

my $xmlStr = "<datalist id=\"stationList\">";

for (my $i = 0; $i < $n; $i++) {
    my $stationName = $stationNameNodes->item($i)->getFirstChild()->toString();
    $xmlStr .= "<option value=\"$stationName\"/>";
}
$xmlStr .= "</datalist>";
my $xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>';
$xmlStr = $xmlHeader . $xmlStr;

print "Content-type: text/xml\n\n";
print $xmlStr;