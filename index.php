<?php

use \Core\Core;

require __DIR__ . '/Core.php';
require __DIR__ . '/expenses.php';

$url = 'https://b24-hpr358.bitrix24.ru/rest/1/dh1nfdj14atiz2do';

$elementId = $_POST['document_id']['2'];

$listsElement = Core::BT24(
    'lists.element.get',
    [
        'IBLOCK_TYPE_ID' => 'lists',
        'IBLOCK_ID' => '27',
        'ELEMENT_ID' => $elementId
    ],
    $url
)['result'][0];

$dealId = '';

foreach ($listsElement['PROPERTY_103'] as $key => $value)
{
    $dealId = $value;
}

$deal = getDeal($dealId, $url);

$opportunity = $deal['OPPORTUNITY'];

if (array_key_exists($elementId ,$expenses[$dealId])){
    $suma = 0;

    if (isset($listsElement['PROPERTY_101'])) {
        foreach ($listsElement['PROPERTY_101'] as $key => $value) {
            $suma += $value;
        }
    }

    if ($suma != $expenses[$dealId][$elementId]) {

        $expenses[$dealId][$elementId] = $suma;
        Core::WriteVariableToFile('expenses', $expenses);

        foreach ($expenses[$dealId] as $elementId => $expense) {
            $totalExpenses += $expense;
        }

        $profit = $opportunity - $totalExpenses;

        $updateDeal = updateDeal($dealId, $url, $profit, $totalExpenses);
    }
}

if (!isset($expenses[$dealId])) {
    $profit = '';
    $totalExpenses = '';

    $suma = 0;

    if (isset($listsElement['PROPERTY_101'])) {
        foreach ($listsElement['PROPERTY_101'] as $key => $value) {
            $suma += $value;
        }
    }

    $totalExpenses = $suma;

    $profit = $opportunity - $totalExpenses;

    $updateDeal = updateDeal($dealId, $url, $profit, $totalExpenses);

    $expenses[$dealId][$elementId] = $suma;
    Core::WriteVariableToFile('expenses', $expenses);
}

if (array_key_exists($dealId ,$expenses) && !array_key_exists($elementId ,$expenses[$dealId])){
    $profit = '';
    $totalExpenses = $deal['UF_CRM_1599645571288'];

    $suma = 0;

    if (isset($listsElement['PROPERTY_101'])) {
        foreach ($listsElement['PROPERTY_101'] as $key => $value) {
            $suma += $value;
        }
    }

    if ($totalExpenses != '') {
        $totalExpenses += $suma;
    } else {
        $totalExpenses = $suma;
    }

    $profit = $opportunity - $totalExpenses;

    $updateDeal = updateDeal($dealId, $url, $profit, $totalExpenses);

    $expenses[$dealId][$elementId] = $suma;
    Core::WriteVariableToFile('expenses', $expenses);
}


function getDeal($dealId, $url)
{
    return Core::BT24(
        'crm.deal.get',
        [
            'id' => $dealId
        ],
        $url
    )['result'];
}

function updateDeal($dealId, $url, $profit, $totalExpenses)
{
    return Core::BT24(
        'crm.deal.update',
        [
            'id' => $dealId,
            'fields' => [
                'UF_CRM_1599645571288' => $totalExpenses,
                'UF_CRM_1599645299680' => $profit
            ]
        ],
        $url
    );
}
