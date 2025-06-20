<?php

namespace App\Http\HttpResponse;

class RespState
{
    const Success = '0';
    const Fail = '999';
    const PlatformMaintenance = 'S001';
    const InvalidMerchant = 'S002';
    const InvalidRequestParam = 'S003';


    const InvalidPassword = 'E100';


    const TransferError = 'E200';
    const TransferAmountError = 'E201';

    const MemberNotExist = 'E300';
    const MemberStatusFail = 'E301';
    const MemberOutOfBalance = 'E302';
}
