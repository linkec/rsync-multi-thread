# Rsync-Multi-Thread
A tool written by php to enable multi thread rsync.

# Why need multi thread rsync?
The test based on a G port dedicated server and home broadband ( 200M )
```
[root@localhost rsync-multi-thread]# ping *.*.*.* -c 10
PING *.*.*.* (*.*.*.*) 56(84) bytes of data.
64 bytes from *.*.*.*: icmp_seq=1 ttl=54 time=186 ms
64 bytes from *.*.*.*: icmp_seq=2 ttl=54 time=187 ms
64 bytes from *.*.*.*: icmp_seq=3 ttl=54 time=187 ms
64 bytes from *.*.*.*: icmp_seq=4 ttl=54 time=187 ms
64 bytes from *.*.*.*: icmp_seq=5 ttl=54 time=179 ms
64 bytes from *.*.*.*: icmp_seq=6 ttl=54 time=182 ms
64 bytes from *.*.*.*: icmp_seq=7 ttl=54 time=187 ms
64 bytes from *.*.*.*: icmp_seq=8 ttl=54 time=191 ms
64 bytes from *.*.*.*: icmp_seq=9 ttl=54 time=189 ms
64 bytes from *.*.*.*: icmp_seq=10 ttl=54 time=222 ms

--- *.*.*.* ping statistics ---
10 packets transmitted, 10 received, 0% packet loss, time 9234ms
rtt min/avg/max/mdev = 179.009/190.111/222.937/11.442 ms
```
Let's do a single thread to test the speed of rsync to transfer.
```
rsync -a *.*.*.*::home /home/
```
The result form iftop
```
*.*.*.*         => *.*.*.*          3.94Mb  2.68Mb  2.56Mb
                <=                  71.6Mb  48.5Mb  46.4Mb    
```


Let's do a multi thread task to test the speed of rsync to transfer.
```
php rsync.php rsync -a *.*.*.*::home /home/ --multi-thread=16 --multi-level=1
```
The result form iftop
```
*.*.*.*         => *.*.*.*          9.95Mb  6.50Mb  1.66Mb
                <=                  180Mb   177Mb   176Mb    
```
Because of my ISP only 200M , so that is almost maximum.

# Usage
```
php rsync.php {RSYNC COMMAND} [--multi-thread=16] [--multi-level=1]
```
## --multi-thread=[number]
How many threads you want to use for rsync instance.
## --multi-level=[numer]
The folder deep of rsync to scan for multi thread.

# Install
```
yum install -y php-cli php-process
```