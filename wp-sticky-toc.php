<?php
/*
Plugin Name: WP Stiky TOC
Plugin URI: https://github.com/itogen/wp-sticky-toc
Description: This Wordpress plugin provides a sticky and reactive TOC (table of contents) on sidebar widget.
Version: 1.0
Author: Gen Ito
Author URI: https://genchannel.net
License: GPL3
*/
//実行するよ!
$HbdDailyWidgetObj = new Hbd_Daily_Widget();


class  Hbd_Daily_Widget extends WP_Widget
{

    private     $widget_ops;
    private     $control_ops;
    public      $id           = "";
    public      $name         = "";
    public      $title        = "";
    public      $text         = "";
    private     $instance;
    private     $new_instance;
    private     $old_instance;
    private     $args;



    public function __construct()
    {
        //ウィジェットスペースのアクション実行
        add_action('widgets_init', array($this, 'hbd_daily_widget'));


        //ウィジェットオプション
        $this->widget_ops  = array('description' => 'Daily Contents');
        $this->control_ops = array('width' => 200, 'height' => 350);

        //ウィジェットを登録
        parent::__construct(
            daily_widget, // Base ID
            'Daily', //name
            $this->widget_ops,
            $this->control_ops
        );
    }


    //ウィジェット、ウィジェットスペースの設定と登録
    public function hbd_daily_widget()
    {
            //ウィジェットをWordPressに登録する ※PHP5.3以上
            register_widget( 'Hbd_Daily_Widget' );

            //ウィジェットスペースの設定
            register_sidebar( array(
                    'name'          => '日替わりコンテンツ',
                    'id'            => 'daily_sidebar',
                    'before_widget' => '<div>',
                    'after_widget'  => '</div>',
                    'before_title'  => '',
                    'after_title'   => ''
            ) );
    }



    // ウィジェット 入力フォーム出力 ==========================================
    /**
    * バックエンドのウィジェットフォーム
    *
    * @see WP_Widget::form()
    *
    * @param array $instance データベースからの前回保存された値
    */
    public function form( $instance )
    {
            //タイトルエリア
            $this->instance = $instance;
            if(isset($this->instance['title']) == true ){
                    $this->title = $this->instance['title'];
            }else{
                    $this->title = '';
            }

            $this->id   = parent::get_field_id('title');
            $this->name = parent::get_field_name('title');

            echo '<p>';
            echo 'タイトル：<br/>';
            printf(
                '<input rows="16" cols="45" type="text" id="%s" name="%s" value="%s">',
                $this->id,
                $this->name,
                esc_attr($this->title)
            );
            echo '</p>';


            //テキストエリア
            if(isset($this->instance['text']) == true ){
                    $this->text = $this->instance['text'];
            }else{
                    $this->text = '';
            }

            $this->id   = parent::get_field_id('text');
            $this->name = parent::get_field_name('text');

            echo '<p>';
            echo 'スクリプトタグ：<br/>';
            printf(
                    '<textarea rows="16" cols="45" id="%s" name="%s" >%s</textarea>',
                    $this->id,
                    $this->name,
                    $this->text
            );
            echo '</p>';


    }

    // ウィジェット 入力フォーム出力 ここまで ==========================================



    /**
    * ウィジェットフォームの値を保存用にサニタイズ
    *
    * @see WP_Widget::update()
    *
    * @param array $new_instance 保存用に送信された値
    * @param array $old_instance データベースからの以前保存された値
    *
    * @return array 保存される更新された安全な値
    */
    public function update( $new_instance, $old_instance ) {
            $this->new_instance = $new_instance;
            return $this->new_instance;
    }



    /**
    * ウィジェットのフロントエンド表示
    *
    * @see WP_Widget::widget()
    *
    * @param array $args     ウィジェットの引数
    * @param array $instance データベースの保存値
    */
    public function widget($args, $instance)
    {
            $this->args     = $args;
            $this->instance = $instance;

            echo $this->args['before_widget'];
            echo $this->args['before_title'];
            echo esc_html($this->instance['title']);
            echo $this->args['after_title'];
            $this->render_contents_list_today();
            echo $this->instance['text'];
            echo $this->args['after_widget'];

            wp_enqueue_style('hbd-daily-contents', get_settings('site_url').'/wp-content/plugins/hbd-daily-contents/style.css');
            wp_enqueue_script('hbd-daily-contents', get_settings('site_url').'/wp-content/plugins/hbd-daily-contents/script.js');
    }

    public function get_contents_list_today()
    {
        $args = [
            'posts_per_page' => 5,
            'post_type'      => ['daily', 'birthdays'],
            'orderby'        => 'meta_value_num',  //日付順
            'order'          => 'DESC', //降順
            'meta_key'       => 'priority',
            'meta_query'     => [
                [
                    'key'    => 'month',
                    'value'  => $_GET['hbd_month'] ?? date('n'),
                    'type'   => 'numeric',
                ],
                [
                    'key'    => 'day',
                    'value'  => $_GET['hbd_day'] ?? date('j'),
                    'type'   => 'numeric',
                ],
            ],
        ];
        return new WP_Query($args);
    }

    public function render_contents_list_today(){
        echo '<div class="hbd-daily-contents">';
        $query =  $this->get_contents_list_today();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                ?>
                <div>
                    <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class="hbd-thumnail-link">
                        <div class="hbd-thumbnail-caption">Happy Birthday <?php $this->render_hbd_emotiocon(); ?></div>
                        <?php if(has_post_thumbnail()): ?>
                            <?php the_post_thumbnail(['300','300']); ?>
                        <?php else: ?>
                            <img src="<?php echo get_settings('site_url').'/wp-content/plugins/hbd-daily-contents/hbd_cake.jpg';?>" alt="no image">
                        <?php endif; ?>
                    </a>
                    <div class="hbd-headline"><?php the_title(); ?></div>
                    <div class="hbd-description"><?php the_content(); ?></div>
                </div>

                <?php
            }
        }
        wp_reset_postdata();
        echo '</div>';
    }
}