<?php

class Actions {
    static $actionsArray;
    public static function getParam($param) {
        return self::$actionsArray[$param];
    }
    public static function setParam($param, $svalue, $lvalue) {
        self::$actionsArray[$param] = array('action_name'=>$svalue, 'action_description'=>$lvalue);
    }
}
// Действия с талонами в очереди
Actions::setParam('BOOK', 'Талон забронирован', 'Бронирование талона');
Actions::setParam('RSTR', 'Талон зарегистрирован', 'Регистрация талона в общей очереди');
Actions::setParam('CNGD', 'Талон изменен', 'В талон внесены изменения');
Actions::setParam('WAIT', 'Ожидается', 'Вызов талона');
Actions::setParam('PCSS', 'Обслуживается', 'Процесс обслуживания талона');
Actions::setParam('CANC', 'Завершен', 'Конец обслуживания талона');
Actions::setParam('REFD', 'Отклонен', 'Отказ в обслуживании талона');
Actions::setParam('PERS', 'Перенаправлен', 'Талон помещен в персональную очередь оператора');
Actions::setParam('RDRR', 'Перенаправлен', 'Талон перенаправлен к другому оператору с возвратом');
Actions::setParam('RDRC', 'Перенаправлен', 'Талон перенаправлен к другому оператору без возврата');

// Действия пользователей по расписанию
Actions::setParam('UBGN', 'Начало действия', '');
Actions::setParam('UEND', 'Начало действия', '');

// Действия пользователей по расписанию
Actions::setParam('DOCP', 'Подача документов', '');
Actions::setParam('DOCR', 'Получение документов', '');
Actions::setParam('CONS', 'Консультация', '');