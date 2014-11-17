/*
 * =====================================================================================
 *
 *       Filename:  get_neighbor_feeds.c
 *
 *    Description:  get neighbor feeds  tools
 *
 *        Version:  1.0
 *        Created:  11/17/2014 02:41:23 PM
 *       Revision:  none
 *       Compiler:  gcc
 *
 *         Author:  chriswei 
 *   Organization:  
 *
 * =====================================================================================
 */

#include "geohash.h"

#include <iostream>
#include  <string>
#include <math.h>
#include <mysql/mysql.h>
#include <map>
#include <vector>
#include <stdlib.h>
using namespace std;

#define EARTH_RADIUS  6378.137
#define PI 3.1415926



typedef struct {
double lng;
double lat;
string  content;  
double dis;
}Feed; 

typedef  map<string,vector<Feed> > HASH_CONTAINER; 

double rad(double d)
{
    return d*PI/180.0;
}
HASH_CONTAINER Hash;
MYSQL mysql;
#define direction 6

void init()
{
    mysql_init(&mysql);
    mysql_real_connect(&mysql,"localhost","root","root","weibo",0,NULL,0);
    int ret = mysql_set_character_set(&mysql,"utf8");
    
}


//初始化geo  hash表
void construct_geo_hash()
{
    init();
    MYSQL_RES  *result;
    MYSQL_ROW row;
    mysql_query(&mysql,"select X(address_loc),Y(address_loc),content from weibo");
    result = mysql_store_result(&mysql);
    unsigned  int i = 0;
    while((row=mysql_fetch_row(result)))
    {
        Feed  feed;
       // POINT(x,y)  x is lat ,y is lng
         feed.lat  =  atol(row[0]);
        feed.lng  =  atol(row[1]);
        feed.content  =  row[2];
        char *hash = geohash_encode(feed.lat,feed.lng,direction);
        vector<Feed>& node = Hash[string(hash)];
        node.push_back(feed); 
        i++;
    }
    cout << "all data record:"<<i<<endl;
    cout<<"hash map size"<<Hash.size()<<endl;

    mysql_free_result(result);
    mysql_close(&mysql);
}


double distance(double lat1,double lng1,double lat2,double lng2)
{
     double radLat1 = rad(lat1); 
     double radLat2 =  rad(lat2);
     double a = radLat1 - radLat2;
     double b = rad(lng1) - rad(lng2);
     double s = 2*asin(sqrt(pow(sin(a/2),2)+cos(radLat1)*cos(radLat2)*pow(sin(b/2),2)));
     s = s*EARTH_RADIUS;
     return s;
}

int main(int argc,char **argv)
{
    vector<Feed>  result;
     construct_geo_hash();
     if(argc != 4 )  
     {
         cout <<"argc param num limit ,must input lng & lat"<<endl;
         return -1;
     }
    double lng = atol(argv[1]);
    double lat = atol(argv[2]);
    char *hash = geohash_encode(lat,lng,direction);
    //获取neighbor 结点
    char** neighbors = geohash_neighbors(hash);
    //把所有的neighbor结点的数据全检索出来
    vector<vector<Feed> > neighbor_node; 
    for(unsigned int i = 0; i < 8; i++)
    {
        vector<Feed>& node = Hash[neighbors[i]]; 
        neighbor_node.push_back(node);
    }
    //放入结点所在中心区块
    neighbor_node.push_back(Hash[hash]);
    
    unsigned int size = neighbor_node.size();
    for(unsigned int i = 0 ; i < size; i++)
    {
        unsigned int node_size = neighbor_node[i].size();
        vector<Feed>& node = neighbor_node[i];
        for(unsigned int k = 0; k < node_size; k++ ) 
        {
             Feed feed = node[k]; 
             feed.dis = distance(lat,lng,feed.lat,feed.lng);   
             result.push_back(feed);
             cout <<"dis:"<<feed.dis<<endl;
        }
    }
    cout << "result size:"<<result.size()<<endl;
}
