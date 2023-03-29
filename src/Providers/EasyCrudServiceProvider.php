<?php

 namespace  Prolaxu\EasyCrud;
 use  Illuminate\Support\ServiceProvider;
 use  Prolaxu\EasyCrud\QueryServiceProvider;
 class  EasyCrudServiceProvider  extends  ServiceProvider
{
     /**
     * Register services.
     */
     public   function  register():  void
    {
         $this ->app->register(QueryServiceProvider::class);
    }

     /**
     * Bootstrap services.
     */
     public   function  boot():  void
    {
         //
    }
}
