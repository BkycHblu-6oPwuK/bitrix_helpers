<?
namespace Beeralex\Catalog\Enum;

enum SaleStatuses : string
{
    /** Комплектация заказа */
    case DA = 'DA';
    /** Отгружен */
    case DF = 'DF';
    /** Ожидаем приход товара */
    case DG = 'DG';
    /** Ожидает обработки */
    case DN = 'DN';
    /** Передан в службу доставки */
    case DS = 'DS';
    /** Ожидаем забора транспортной компанией */
    case DT = 'DT';
    /** Выполнен */
    case F = 'F';
    /** Принят, ожидается оплата */
    case N = 'N';
    /** Оплачен, формируется к отправке */
    case P = 'P';
}