<?php

require_once libfile('function/misc');

function getIPLocationByAddr($ipAddr){
    return convertip($ipAddr);
}