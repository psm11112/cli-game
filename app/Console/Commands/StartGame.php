<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;


class StartGame extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'StartGame';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Game of User Can Make Bet and Win Point';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {



        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('email',128)->unique();
                $table->string('password');
                $table->string('points');
                $table->integer('bet_count')->nullable(true);
                $table->timestamps();
            });

        }





        $this->info(">>>>>>>Well come<<<<<<<<");
        $type=$this->ask("New User Enter:-1 or Login Enter 2");

        $userModel=new User();

        if($type==1){

            $name=$this->ask("Enter User Name");
            $email=$this->ask("Enter Email");
            $password=$this->secret('Enter Password');


            if($name=="" || is_null($name)){
                $this->error('name is required');
                exit;
            }
            if($email=="" || is_null($email)){

                $this->error('email is required');
                exit;
            }else{
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->error('Invalid email format');
                    exit;
                }else{

                    $user=$userModel->getUserByEmail($email);
                    if(!is_null($user)){
                        $this->error('Email already exists');
                        exit;
                    }


                }
            }

            $userModel->name=$name;
            $userModel->email=$email;
            $userModel->password=Hash::make($password);
            $userModel->points=1000;

            $userModel->save();

            $this->info("You Have Been Successfully Registered!");
            $type=$this->ask("Are you  want sign into account enter:2 or Exit enter:0");

        }


        if($type==2)
        {

            $email=$this->ask("Enter Email");
            $password=$this->secret('Enter Password');
            $user=$userModel->getUserByEmail($email);


            if(!is_null($user)){
                if(Hash::check($password, $user->password)){

                    Session::put('user_id',$user->id);
                    $this->info("Well Come:----".$user->name);


                    if($this->confirm("You Want to Bet")){
                        $randomArray=[1,0,2,0,3,0,4,0,5,0];
                        start:


                        if($user->bet_count<1000){

                            $wallet=($user->points+array_rand($randomArray)*10);
                            $user=$userModel->getUser($user->id);
                            $user->points=$wallet;
                            $user->bet_count=$user->bet_count+1;
                            $user->save();
                            $this->info("User Point:--".$wallet);


                            if($this->confirm("You Want to Continues ")){
                                goto start;
                            }

                        }else{

                            $this->error('Bets not more than 1000');
                        }
                    }

                }else{

                    $this->error('Your username or password was incorrect.');
                }
            }else{

                $this->error('something wrong please try again');
            }

        }else{
            exit;
        }


    }
}
