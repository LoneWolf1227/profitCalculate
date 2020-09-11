<?php

use \Core\Core;

require __DIR__ . '/Core.php';
require __DIR__ . '/expenses.php';

$url = '';

$elementId = $_POST['document_id']['2'];
//Берём элемент из списка
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
//Берём ID сделки прикреплённой к элементу
foreach ($listsElement['PROPERTY_103'] as $key => $value)
{
    $dealId = $value;
}
//Берём сделку по ID
$deal = getDeal($dealId, $url);
//Берём цену вставленную в сделке
$opportunity = $deal['OPPORTUNITY'];

/*
 * Если элемент списка обновлён.
 * Проверяем элемент на существования если элемент существует в массиве расходов то
 * обновляем его значения и пересчитываем доходи и расходы и обновляем поля в сделке
 */
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

/*
 * Когда в списке расходов пусто то сработает этот блок.
 * Если в массиве расходов не существует сделка то оно добавляется в наш массив.
 * и поля расходов будут добавлен в массив.
 */
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
/*
 * В массиве расходов проверяется если сделка существует но элемент который добавлен не существует
 * то элемент добавляется в массив расходов и пересчитается расходы и доходы и обновляется сделка.
 */
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

//Функция берёт сделку по ID
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
//Функция обновляет сделку
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
