#!/usr/bin/expect
set timeout -1
spawn ssh u835731528@pkastro.com
expect "password:"
send "Lrpvaa@123\r"
expect "$ "
send "ls\r"
expect "$ "
send "exit\r"
