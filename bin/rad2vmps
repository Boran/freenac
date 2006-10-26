#!/usr/bin/perl

# 
# Originally from OpenVMS distribution
# 2006.01.11/Sean <1> :add performance measurements
# 2006.09.10/Hector <2> :Adaptation to work with FreeRadius
#-------------------------------

#use Benchmark;   #<1>

use Socket;
$|=0;
#$DEBUG=1;
$DEBUG=0;
#use strict;
use vars qw(%RAD_REQUEST %RAD_REPLY %RAD_CHECK); 
use Data::Dumper; 

use constant RLM_MODULE_REJECT=>0;
use constant RLM_MODULE_FAIL=>1; 
use constant RLM_MODULE_OK=>2;	
use constant RLM_MODULE_HANDLED=>3;
use constant RLM_MODULE_INVALID=>4;
use constant RLM_MODULE_USERLOCK=>5;
use constant RLM_MODULE_NOTFOUND=>6;
use constant RLM_MODULE_NOOP=>7;
use constant RLM_MODULE_UPDATED=>8;
use constant RLM_MODULE_NUMCODES=>9;

$suplicant_mac='';
$switch_ip='';

sub formatItem($$) {

	my $mybuf;
	undef($mybuf);

	$itemheader = shift;
	$itemvalue = shift;

	$mybuf = $mybuf . pack("H*",(unpack("a*",$itemheader))); # Add header 

	$payload = pack("a*",(unpack("a*",$itemvalue)));
	$length=length($payload);
	$length= pack("H*",(unpack("a*",sprintf("%04x",$length))));

	$mybuf = $mybuf . $length . $payload; # Add payload + length

	return $mybuf;
}

sub makeVQPrequest($) {

	my $request = $_;
	my $buf;

	# Header...
	$buf = $buf . pack("H*",(unpack("a*","01"))); # Header bit

	# Is a request to join a vlan
	$buf = $buf . pack("H*",(unpack("a*","01"))); # Is a request

	# No error
	$buf = $buf . pack("H*",(unpack("a*","00"))); # No error

	# 6 data items in inbound payload
	$buf = $buf . pack("H*",(unpack("a*","06")));

	# Sequence number of request
	$buf = $buf . pack("H*",(unpack("a*","000 1234"))); # Bogus sequence number

	# Add Client switch IP
	$buf = $buf . formatItem("000 0c01",(sprintf("%s",unpack("a*",inet_aton($request{client_ip})))));

	# Add Port Name
	$buf = $buf . formatItem("000 0c02",$request{port_name}); # Payload 

	# Add VLAN to confirm to buffer
	$buf = $buf . formatItem("000 0c03",$request{vlan}); # Payload 

	# Add VTP domain name
	$buf = $buf . formatItem("000 0c04",$request{vtp_domain}); # Payload 

	# Add UNKNOWN data to buffer...
	$buf = $buf . pack("H*",(unpack("a*","000 0c07"))); # Header
	$buf = $buf . pack("H*",(unpack("a*","0001 0"))); # Unknown filler

	# Add MAC address to buffer
	$buf = $buf . formatItem("000 0c06",sprintf("%s",pack("H*",(unpack("a*",$request{macaddr}))))); # Payload 

	return "$buf";
}

sub sendVQP($$) {

	my $PORTNO="1589";
	my $HOSTNAME= shift;
	my $buf = shift;

	if ($DEBUG==1) {
		print "==============================\n";
		print "MESSAGE SENT:\n";
		open (HEX, "|/usr/bin/hexdump");
		select HEX;
		print $buf;
		close HEX;
		select STDOUT;
		print "==============================\n";
	}

	socket(SOCKET, PF_INET, SOCK_DGRAM, getprotobyname("udp")) or die "socket: $!";

	my $ipaddr   = inet_aton($HOSTNAME);
	my $portaddr = sockaddr_in($PORTNO, $ipaddr);
	send(SOCKET, $buf, 0, $portaddr) == length($buf)
	        or die "cannot send to $HOSTNAME($PORTNO): $!";

	$portaddr = recv(SOCKET, $buf, 1500, 0); # or die "recv: $!";

	if ($DEBUG==1) {
		print "MESSAGE RECV:\n";
		open (HEX, "|/usr/bin/hexdump");
		select HEX;
		print $buf;
		close HEX;
		select STDOUT;
		print "==============================\n";
	}
	return "$buf";
}

sub parseVQPresp($) {

	my %response = (
		status		=>	"",
		vlan 		=>	"",
		macaddr		=>	"",
	);

	my $buf = shift;
	$buf =~ /^(.)(.)(.)(.)(....)/;
	my ($header,$type,$status,$size,$sequence) = 
		(ord($1),ord($2),ord($3),ord($4),pack("a*",(unpack("H*",$5))));

	$buf =~ s/^........//;

	$response{status}="ALLOW" if ($status == 0);
	$response{status}="DENY" if ($status == 3);
	$response{status}="SHUTDOWN" if ($status == 4);
	$response{status}="WRONG_DOMAIN" if ($status == 5);

	for ($i=1;$i<=$size;$i++) {

		$payload_type=pack("a*",(unpack("H*",substr($buf,0,4))));
		$payload_size=sprintf("%d",hex(pack("a*",(unpack("H*",substr($buf,4,2))))));
		$payload=substr($buf,6,$payload_size);

		if ($payload_type eq "00000c03") {
			$response{vlan}=$payload;
		} elsif ($payload_type eq"00000c08") {
			$response{macaddr}=pack("a*",(unpack("H*",$payload)));
		}
		substr($buf,0,($payload_size + 6)) = "";
	}
	return %response;
}

sub authorize()			
{
   if ((!$RAD_REQUEST{'Calling-Station-Id'} && !$RAD_REQUEST{'NAS-IP-Address'}))
   {
      return RLM_MODULE_NOOP;
   }
   else
   {
      my $mac=$RAD_REQUEST{'Calling-Station-Id'};	
      @temp;	
      @temp=split(/-/,$mac);	
      $mac=@temp[0].@temp[1].@temp[2].@temp[3].@temp[4].@temp[5];
      $suplicant_mac=$mac;
      $switch_ip=$RAD_REQUEST{'NAS-IP-Address'};
      return RLM_MODULE_OK;
   }
}

sub post_auth()
{
   $request{macaddr}=$suplicant_mac;			
   $request{client_ip}=$switch_ip;
   #$request{server_ip}='freenac';		
   $request{server_ip}='localhost';		
   $request{port_name}='Fa0/1';		
   $request{vlan}='';		
   $request{vtp_domain}='';

   $buf = makeVQPrequest(%request);
   $buf = sendVQP($request{server_ip},$buf);
   %response = parseVQPresp($buf);
   $last_count=$counter;
   
   if ( ! $response{vlan} )
   {
      return RLM_MODULE_REJECT;
   }
   else 
   {	
      $RAD_REPLY{'Tunnel-Type'} = 'VLAN';
      $RAD_REPLY{'Tunnel-Medium-Type'} = '6';
      $RAD_REPLY{'Tunnel-Private-Group-ID'} = $response{vlan};
      return RLM_MODULE_OK;
   }
}
