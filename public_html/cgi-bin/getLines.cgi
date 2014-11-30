#!/usr/bin/perl
#
# @File getLines.pl
# @Author mengz
# @Created Nov 25, 2014 9:23:27 PM
#

use strict;
use Encode;
#use utf8;
use XML::DOM;

my $buffer = $ENV{QUERY_STRING};
#my $buffer = "date=20141121&bus=粤BBBBBB";
# Split information into name/value pairs
my @pairs = split(/&/, $buffer);

my @datePair = split(/=/,$pairs[0]);
my @busPair = split(/=/,$pairs[1]);
my $date = $datePair[1];
my $bus = $busPair[1];
#my $date = "20141121";
#my $bus = "粤BAAAAA";

use constant {
	BANCI => "班次",
	ZHUANGTAI => "状态",
	SHENHEREN => "审核人",
	SHENHE => "审核",
	WEISHENHE => "未审核",
	YISHEHE => "已审核",
	CHONGSHENHE => "重审核",
};

my $numberOfFiles;
my $dataDir = "../linedata/$date/";
my @fileList = ();
if(opendir DIR, "$dataDir") {
	@fileList = grep /^$bus/, readdir DIR;
	closedir DIR;
	$numberOfFiles = @fileList;
} else {
	$numberOfFiles = 0;
}

#print "$numberOfFiles\n";
my $strRet = "";
if ($numberOfFiles != 0) {
	my $xmlParser = new XML::DOM::Parser;
	my $xmlDoc;
	$strRet = "<tr><th>" . Encode::decode_utf8(BANCI) . "</th><th>" . Encode::decode_utf8(ZHUANGTAI) . "</th><th>" . Encode::decode_utf8(SHENHEREN) . "</th><th>" . Encode::decode_utf8(SHENHE) . "</th></tr>";
	my $idStr = "";
	my $checker = "";
	foreach (@fileList) {
		$xmlDoc = $xmlParser->parsefile($dataDir . $_);
		$idStr = $xmlDoc->getElementsByTagName('Id')->item(0)->getFirstChild()->toString();
		my $checkerNodes = $xmlDoc->getElementsByTagName('Checker');
		if ($checkerNodes->getLength == 0) {
			$strRet .= "<tr><td>" . $idStr . "</td><td class=\"redColor\">" . Encode::decode_utf8(WEISHENHE) . "</td><td></td><td><a href=\"linecheck.html?id=$idStr\">" . Encode::decode_utf8(SHENHE) . "</a></td></tr>";
		} else {
			$checker = $checkerNodes->item(0)->getElementsByTagName('Name')->item(0)->getFirstChild()->toString();
			$strRet .= "<tr><td>" . $idStr . "</td><td class=\"greenColor\">". Encode::decode_utf8(YISHEHE) . "</td><td>$checker</td><td><a href=\"linecheck.html?id=$idStr\">" . Encode::decode_utf8(CHONGSHENHE) . "</a></td></tr>";
		}
		$xmlDoc->dispose;
	}
} else {
	$strRet = "NULL";
}

print "Content-type: text/plain\n\n";
print $strRet;