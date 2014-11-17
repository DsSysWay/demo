#include <stdio.h>
#include <mysql/mysql.h>


int main(int argc,char** argv)
{
    MYSQL mysql;
    MYSQL_RES  *result;
    MYSQL_ROW row;
    mysql_init(&mysql);
    mysql_real_connect(&mysql,"localhost","root","root","weibo",0,NULL,0);
    int ret = mysql_set_character_set(&mysql,"utf8");
    mysql_query(&mysql,"select X(address_loc),Y(address_loc),content from weibo limit 1");
    result = mysql_store_result(&mysql);
    while((row=mysql_fetch_row(result)))
    {
        fprintf(stdout,"id:%s--%s -- %s",row[0],row[1],row[2]);
    }
    mysql_free_result(result);
    mysql_close(&mysql);
    return 0;
}
