<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;


class Packages extends Model
{
    //

    use SoftDeletes;

    protected $table = 'packages' ;

    protected $fillable = ['package','pv','rs','amount','code','level_percent'];

    public static function TopUPAutomatic($user_id){
    	$user_detils = User::find($user_id);
    	$balance = Balance::where('user_id',$user_id)->pluck('balance');
    	$package = self::find($user_detils->package);

    	if($package->amount <= $balance){

    		Balance::where('user_id',$user_id)->decrement('balance',$package->amount);
    		PurchaseHistory::create([
                'user_id'=>$user_id,
    			'package_id'=>$user_detils->package,
    			'count'=>$package->top_count,
    			'total_amount'=>$package->amount,
    			]);
    		 User::where('id',$user_id)->increment('revenue_share',$package->rs);

             RsHistory::create([
                    'user_id'=> $user_id ,
                    'from_id'=> $user_id ,
                    'rs_credit'=> $package->rs ,
                    ]);


    		 /* Check for rank upgrade */

    		 Ranksetting::checkRankupdate($user_id,$user_detils->rank_id);

    		return true;

    	}else{
    		return flase ; 
    	}
    }

    public static function levelCommission($user_id,$package_am){

       $user_arrs=[];
       $results=SELF::gettenupllins($user_id,1,$user_arrs);
          foreach ($results as $key => $upuser) {
              $package=ProfileInfo::where('user_id',$upuser)->value('package');
              $pack=Packages::find($package);
              $level_commission=$package_am*$pack->level_percent*0.01;
                $commision = Commission::create([
                'user_id'        => $upuser,
                'from_id'        => $user_id,
                'total_amount'   => $level_commission,
                'tds'            => 0,
                'service_charge' =>0,
                'payable_amount' => $level_commission,
                'payment_type'   => 'level_commission',
                'payment_status' => 'Yes',
          ]);
          /**
          * updates the userbalance
          */
          User::upadteUserBalance($upuser, $level_commission);
          self::checkRankbasedCommission($upuser,$package_am,$user_id);
          }

    }

    public static function checkRankbasedCommission($user,$amount,$from){
        $rank=User::find($user)->rank_id;
        if($rank > 1){
           $rankgain=Ranksetting::find($rank)->gain;
           $rank_commission=$amount*$rankgain*0.01;
                    $commision = Commission::create([
                    'user_id'        => $user,
                    'from_id'        => $from,
                    'total_amount'   => $rank_commission,
                    'tds'            => 0,
                    'service_charge' =>0,
                    'payable_amount' => $rank_commission,
                    'payment_type'   => 'rank_level_commission',
                    'payment_status' => 'Yes',
              ]);
              User::upadteUserBalance($user, $rank_commission);

          }
     }
     public static function directReferral($sponsor,$from,$package){
          
          $pack=Packages::find($package);
          $direct_ref=Settings::find(1)->direct_referral;
          $direct_referral=$pack->amount*$direct_ref*0.01;
          $commision = Commission::create([
                'user_id'        => $sponsor,
                'from_id'        => $from,
                'total_amount'   => $direct_referral,
                'tds'            => 0,
                'service_charge' =>0,
                'payable_amount' => $direct_referral,
                'payment_type'   => 'direct_referral',
                'payment_status' => 'Yes',
          ]);
          /**
          * updates the userbalance
          */
          User::upadteUserBalance($sponsor, $direct_referral);
          // self::checkRefreals($sponsor,$from,$package);

    }

    public static function checkRefreals($sponsor,$from,$package){
      $usercount=Sponsortree::where('sponsor',$sponsor)->where('type','yes')->count('user_id');
      if($usercount > 3){
          $pack=Packages::find($package);
          $direct_ref=Settings::find(1)->three_friends;
          $direct_referral=$pack->amount*$direct_ref*0.01;
          $commision = Commission::create([
                'user_id'        => $sponsor,
                'from_id'        => $from,
                'total_amount'   => $direct_referral,
                'tds'            => 0,
                'service_charge' =>0,
                'payable_amount' => $direct_referral,
                'payment_type'   => 'three_referral',
                'payment_status' => 'Yes',
          ]);
          User::upadteUserBalance($sponsor, $direct_referral);

            if($usercount > 8){
              $eight_friends=Settings::find(1)->three_friends;
              $eight_referral=$pack->amount*$eight_friends*0.01;
              $commision = Commission::create([
                    'user_id'        => $sponsor,
                    'from_id'        => $from,
                    'total_amount'   => $eight_referral,
                    'tds'            => 0,
                    'service_charge' =>0,
                    'payable_amount' => $eight_referral,
                    'payment_type'   => 'eight_referral',
                    'payment_status' => 'Yes',
              ]);
              User::upadteUserBalance($sponsor, $eight_referral);
            }

      }
    }


    public static function gettenupllins($upline_users,$level=1,$uplines){
     if ($level > 10) 
        return $uplines;  
   
     $upline=Tree_Table::where('user_id',$upline_users)->where('type','=','yes')->value('placement_id'); 

      if ($upline > 0)
          $uplines[]=$upline;

     if ($upline == 1) 
       
        return $uplines;  
    
     return SELF::gettenupllins($upline,++$level,$uplines);
   }

  

public static function rankCheck($rankuser){
  $cur_rank=User::find($rankuser)->rank_id;
  $next_rank=$cur_rank+1;
  $rank_det=Ranksetting::find($next_rank);
  $user_count=Sponsortree::where('sponsor',$rankuser)->where('type','yes')->count('user_id');
    if($rank_det->minimum_ref_for_each1 == 0 && $rank_det->minimum_direct_ref2 == 0 && $rank_det->minimum_direct_ref3 == 0 && $user_count >= $rank_det->minimum_direct_ref1){
        Ranksetting::insertRankHistory($rankuser,$next_rank,$cur_rank,'rank_updated');
    }

    if($rank_det->minimum_ref_for_each1 > 0){

      // dd($rank_det);
       
        $direct_ref_users1=Sponsortree::join('users','sponsortree.user_id','=','users.id')
                                      ->where('sponsortree.sponsor','=',$rankuser)
                                      ->where('sponsortree.type','=','yes')
                                      ->where('users.referral_count','>=',$rank_det->minimum_ref_for_each1)
                                      ->pluck('users.id');

                                      // dd($rank_det->minimum_ref_for_each1);
        $direct_ref1=count($direct_ref_users1);
              // dd($direct_ref_users1);

          if($direct_ref1 >= $rank_det->minimum_direct_ref1 && $rank_det->minimum_direct_ref2 == 0 && $rank_det->minimum_direct_ref3 == 0){
              Ranksetting::insertRankHistory($rankuser,$next_rank,$cur_rank,'rank_updated');
          }
          if($rank_det->minimum_ref_for_each2 > 0){
              // dd("hello");

              $direct_ref_users2=Sponsortree::join('users','sponsortree.user_id','=','users.id')
                                            ->where('sponsortree.sponsor','=',$rankuser)
                                            ->where('users.referral_count','>=',$rank_det->minimum_ref_for_each2)
                                            ->where('users.referral_count','<',$rank_det->minimum_ref_for_each1)
                                            ->whereNotIn('sponsortree.user_id',$direct_ref_users1)
                                            ->pluck('sponsortree.user_id');
              $direct_ref2=count($direct_ref_users2);
              // dd($direct_ref2);

          if($direct_ref1 >= $rank_det->minimum_direct_ref1 && $direct_ref2 >= $rank_det->minimum_direct_ref2 && $rank_det->minimum_direct_ref3 == 0){
              Ranksetting::insertRankHistory($rankuser,$next_rank,$cur_rank,'rank_updated');
          }
    }
         

          if($rank_det->minimum_ref_for_each3 > 0 && $rank_det->minimum_direct_ref2 == 0){
            // dd($rank_det->minimum_direct_ref1);

            if($direct_ref1 >= $rank_det->minimum_direct_ref1){

              $second_user=self::Levelcount($rankuser,2);
              // dd($rank_det->minimum_direct_ref3);

              if(count($second_user) >= $rank_det->minimum_direct_ref3){

                $sum_three=0;
                foreach ($second_user as $key => $suser) {
                 $ref_count=User::find($suser)->referral_count;
               
                 if($ref_count >= $rank_det->minimum_ref_for_each3){
                  $sum_three=$sum_three+1;
                 }

                }

              if($sum_three >= $rank_det->minimum_direct_ref3){
                 Ranksetting::insertRankHistory($rankuser,$next_rank,$cur_rank,'rank_updated');
              }
            }

            }

          }

  
  
}
}

public static function Levelcount($user_id,$level)
  {
      $first_level = DB::table('sponsortree')
                       ->where('sponsor','=',$user_id)
                       ->where('type','<>',"vaccant")
                       ->select('user_id')
                       ->get(); 
      $first_count = count($first_level);
        if($first_count > 0 ){
          $first_array = [];
            foreach($first_level as $row){
              $first_array[] = $row->user_id;
              }
              $second_level = DB::table('sponsortree')
                                ->whereIn('sponsor',$first_array)
                                ->where('type','<>',"vaccant")
                                ->select('user_id')
                                ->get(); 
              $second_count = count($second_level);
              if($second_count > 0 ){
                $second_array = [];
                  foreach($second_level as $row){
                    $second_array[] = $row->user_id;
                  }
                    return $second_array; 
              }
              else{
                   return 0;
                 }
        }
        else{
                   return 0;
                 }
    }

   

 
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function profileinfo()
    {
        return $this->belongsTo('App\Profileinfo');
    }

    public function PurchaseHistoryR()
    {
        return $this->hasMany('App\PurchaseHistory', 'package_id', 'id');
    }

   
}