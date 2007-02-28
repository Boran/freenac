#!/bin/sh

 grep ALLOCATE a > b
 grep FREE a > c
 cat b | awk '{ print $2 }' | sort > b1
 cat c | awk '{ print $2 }' | sort > c1

