 <?php $__env->startSection('content'); ?> <?php echo $__env->yieldContent('header'); ?> <div class="page-container"> <div class="page-content"> <?php echo $__env->yieldContent('sidebar'); ?> <div class="content-wrapper"> <?php echo $__env->yieldContent('page-header'); ?> <div class="content"> <?php echo $__env->yieldContent('main'); ?> <?php echo $__env->yieldContent('footer'); ?> </div> </div> </div> </div> <?php $__env->stopSection(); ?>
<?php echo $__env->make('app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>