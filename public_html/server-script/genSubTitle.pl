#!/usr/bin/perl
#
# @File genSubTitle.pl
# @Author mengz
# @Created Dec 30, 2014 12:21:57 AM
#

use strict;
use warnings;

sub error_exit {
  die "@_\n";
}

sub usage {
  print "$0 <input_srt> <time_start> <time_end> <output_srt> \n";
  exit 11;
}

my $numOpts = scalar @ARGV;
if ($numOpts != 4) {
  &usage ;
}

print "$numOpts \n";

my ($inputSrtFP, $outputSrtFP, $videoStartTime, $videoEndTime);
$inputSrtFP = $ARGV[0];
$outputSrtFP = $ARGV[3];
$videoStartTime = $ARGV[1];
$videoEndTime = $ARGV[2];
print "$inputSrtFP\n$outputSrtFP\n$videoStartTime\n$videoEndTime\n";

unless ( -e $inputSrtFP ) {
  &error_exit("The file $inputSrtFP does not exist, please check it.");
#  print "Do not exist\n";
}

#print "File exist\n";
my $fs = open FH_INPUT, "<", $inputSrtFP;
unless ($fs) {
  &error_exit("Open file $inputSrtFP failed: $!\nPlease check it.");
}

$fs = open FH_OUTPUT, ">", $outputSrtFP;
unless ($fs) {
  &error_exit("Create $outputSrtFP failed: $!\nPlease check it.");
}

my $n = 0;
while (<FH_INPUT>) {
  my $line = $_;
  if ( length $line != 2 ) {
    print STDOUT ($n . ": " . $_); 
  } else {
	print "------------------\n";
	$n++;
  }
}
