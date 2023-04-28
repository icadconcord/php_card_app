<?php
class Constants
{
    const PUBLICKEY_MODULUS = "a556031686505e1c534b7b9632a4ae892a8bd2452f02f70229527f3726364eb68eb6677571369177c2befdd4b40488d71a1d21a24dc41349aa71c0c7713f56b114a867bad3983850287997d1074f0fba7fbc22796bcbcf5bd63c7933edda1b2dd5ab52f98806c64dfaf3b2fd2154ec883b693c46fde5d091973b6a47f8179b11cd5042016378d582456451cfb864da66c812151e700103c62c5f9c0e8bf6d2aabdde2b80c360f6635b513d28d64dac947cd10aa80827fe4ac4dc78208389d3281176dee53c97c4723c3f4126e06ee5824915e22ff4e7ff572784ee57ae543bcd366fb16401eec8d8d184c2a4fe640db47b659f80348e23acd8575700334ed84d";
    const PUBLICKEY_EXPONENT = "010001";
}


//https://middleware.paysure.ng:8080/api/transfer/list-transactions?applyStatus=false&isPaid=false&startDate=2023-03-14&endDate=2023-03-14 
//{"status":400,"message":"Bad Request","errorEessage":"Unparseable date: \"2023-03-14\""}