<?php
namespace Fontibus\Query;

class Templates {

    public static string $select = 'SELECT %SELECT_FIELDS% FROM %TABLE% %JOIN% %WHERE% %GROUP_BY% %ORDER_BY% %LIMIT% %OFFSET%';
    public static string $update = 'UPDATE %TABLE% SET %UPDATE_FIELDS% %JOIN% %WHERE% %GROUP_BY% %ORDER_BY% %LIMIT% %OFFSET%';
    public static string $delete = 'DELETE FROM %TABLE% %JOIN% %WHERE% %GROUP_BY% %ORDER_BY% %LIMIT% %OFFSET%';
    public static string $insert = 'INSERT INTO %TABLE%(%INSERT_FIELDS%) VALUES %INSERT_VALUES%';

}