<?php

namespace webdb\wiki_utils;

#####################################################################################################

function get_article_record_by_title($title)
{
  $where_items=array();
  $where_items["title"]=$title;
  $records=\webdb\sql\file_fetch_prepare("wiki/get_article_record_by_title",$where_items);
  if (count($records)==1)
  {
    return $records[0];
  }
  return false;
}

#####################################################################################################

function get_file_record_by_title($title)
{
  $where_items=array();
  $where_items["title"]=$title;
  $records=\webdb\sql\file_fetch_prepare("wiki/get_file_record_by_title",$where_items);
  if (count($records)==1)
  {
    return $records[0];
  }
  return false;
}

#####################################################################################################

function wikitext_to_html($content)
{
  $break=\webdb\utils\template_fill("break");

  $escape_pairs=array();
  # order is important
  $escape_pairs["comment"]=array("<!--","-->");
  $escape_pairs["nowiki"]=array("<nowiki>","</nowiki>");
  $escape_pairs["code"]=array("<code>","</code>");

  foreach ($escape_pairs as $key => $pair)
  {
    $content=\webdb\wiki_utils\wikitext_escape_pair($content,$pair[0],$pair[1]);
  }

  $content=str_replace(PHP_EOL,$break,$content);
  $content=str_replace("\r",$break,$content);
  $content=str_replace("\n",$break,$content);
  $content=\webdb\wiki_utils\wikitext_to_html__file($content);
  $content=\webdb\wiki_utils\wikitext_to_html__internal_article_link($content);
  $content=\webdb\wiki_utils\wikitext_to_html__external_article_link($content);

  $pair=$escape_pairs["code"];
  $content=\webdb\wiki_utils\wikitext_unescape_pair($content,$pair[0],$pair[1]);
  $content=\webdb\wiki_utils\wikitext_to_html__code_block($content);

  $pair=$escape_pairs["nowiki"];
  $content=\webdb\wiki_utils\wikitext_unescape_pair($content,$pair[0],$pair[1]);
  $content=\webdb\wiki_utils\wikitext_to_html__nowiki_block($content);

  $pair=$escape_pairs["comment"];
  $content=\webdb\wiki_utils\wikitext_unescape_pair($content,$pair[0],$pair[1]);

  return $content;
}

#####################################################################################################

function wikitext_escape_pair($content,$open_tok,$close_tok)
{
  $parts=explode($open_tok,$content);
  for ($i=1;$i<count($parts);$i++)
  {
    $part=$parts[$i];
    $tokens=explode($close_tok,$part);
    if (count($tokens)<2)
    {
      continue;
    }
    $escape=array_shift($tokens);
    $parts[$i]=$open_tok.rawurlencode($escape).$close_tok.implode($close_tok,$tokens);
  }
  return implode("",$parts);
}

#####################################################################################################

function wikitext_unescape_pair($content,$open_tok,$close_tok)
{
  $parts=explode($open_tok,$content);
  for ($i=1;$i<count($parts);$i++)
  {
    $part=$parts[$i];
    $tokens=explode($close_tok,$part);
    if (count($tokens)<2)
    {
      continue;
    }
    $escape=array_shift($tokens);
    $parts[$i]=$open_tok.rawurldecode($escape).$close_tok.implode($close_tok,$tokens);
  }
  return implode("",$parts);
}

#####################################################################################################

function wikitext_to_html__nowiki_block($content)
{
  $parts=explode("<nowiki>",$content);
  for ($i=1;$i<count($parts);$i++)
  {
    $part=$parts[$i];
    $tokens=explode("</nowiki>",$part);
    if (count($tokens)<2)
    {
      continue;
    }
    $nowiki=array_shift($tokens);
    $parts[$i]=$nowiki.implode("</nowiki>",$tokens);
  }
  return implode("",$parts);
}

#####################################################################################################

function wikitext_to_html__code_block($content)
{
  $parts=explode("<code>",$content);
  for ($i=1;$i<count($parts);$i++)
  {
    $part=$parts[$i];
    $tokens=explode("</code>",$part);
    if (count($tokens)<2)
    {
      continue;
    }
    $code=array_shift($tokens);
    $code_params=array();
    $code_params["code"]=$code;
    $code=\webdb\utils\template_fill("wiki/code_block",$code_params);
    $parts[$i]=$code.implode("</code>",$tokens);
  }
  return implode("",$parts);
}

#####################################################################################################

function wikitext_to_html__internal_article_link($content)
{
  $parts=explode("[[",$content);
  for ($i=1;$i<count($parts);$i++)
  {
    $part=$parts[$i];
    $tokens=explode("]]",$part);
    if (count($tokens)<2)
    {
      continue;
    }
    $token=array_shift($tokens);
    $link=explode("|",$token);
    $link_params=array();
    $link_params["url_title"]=array_shift($link);
    $link_params["caption"]=$link_params["url_title"];
    if (count($link)>0)
    {
      $link_params["caption"]=implode(" ",$link);
    }
    $link=\webdb\utils\template_fill("wiki/internal_article_link",$link_params);
    $parts[$i]=$link.implode("]]",$tokens);
  }
  return implode("",$parts);
}

#####################################################################################################

function wikitext_to_html__file($content)
{
  # todo: \webdb\graphics\scale_img($buffer,$scale,$w,$h);
  # see https://en.wikipedia.org/wiki/Help:Wikitext#Images
  $parts=explode("[[File:",$content);
  for ($i=1;$i<count($parts);$i++)
  {
    $part=$parts[$i];
    $tokens=explode("]]",$part);
    if (count($tokens)<2)
    {
      continue;
    }
    $token=array_shift($tokens);
    $file=explode("|",$token);
    $title=array_shift($file);
    $file_record=\webdb\wiki_utils\get_file_record_by_title($title);
    if ($file_record!==false)
    {
      /*
      $buffer=imagecreatetruecolor($w,$h);
      $bg_color=imagecolorallocate($buffer,255,0,255); # magenta
      imagecolortransparent($buffer,$bg_color);
      imagefill($buffer,0,0,$bg_color);
      $line_color=imagecolorallocate($buffer,0,0,0); # black
      */
    }
    else
    {
      # file record not found
    }
    $href=false;
    if (count($file)>0)
    {
      $link=implode(" ",$file);
      $link=explode("=",$link);
      $link_key=trim(strtolower(array_shift($link)));
      if ((count($link)>0) and ($link_key=="link"))
      {
        $href=implode("=",$link);
      }
    }
    $file_params=array();
    $file_params["img_data"]="";
    $file_params["img_data"]=\webdb\graphics\base64_image($buffer,"png");
    if ($href!==false)
    {
      $file_params["href"]=$href;
      $file=\webdb\utils\template_fill("wiki/file_link",$file_params);
    }
    else
    {
      $file=\webdb\utils\template_fill("wiki/file",$file_params);
    }
    $parts[$i]=$file.implode("]]",$tokens);
  }
  return implode("",$parts);
}

#####################################################################################################

function wikitext_to_html__external_article_link($content)
{
  $parts=explode("[",$content);
  for ($i=1;$i<count($parts);$i++)
  {
    $part=$parts[$i];
    $tokens=explode("]",$part);
    if (count($tokens)<2)
    {
      continue;
    }
    $token=array_shift($tokens);
    $link=explode(" ",$token);
    $link_params=array();
    $link_params["url"]=array_shift($link);
    $link_params["caption"]=$link_params["url"];
    if (count($link)>0)
    {
      $link_params["caption"]=implode(" ",$link);
    }
    $link=\webdb\utils\template_fill("wiki/external_article_link",$link_params);
    $parts[$i]=$link.implode("]",$tokens);
  }
  return implode("",$parts);
}

#####################################################################################################

function get_user_wiki_settings()
{
  global $settings;
  $user_record=\webdb\chat\chat_initialize();
  $data=array();
  if ($user_record["json_data"]<>"")
  {
    $data=json_decode($user_record["json_data"],true);
    if (isset($data["wiki"])==true)
    {
      return $data["wiki"];
    }
  }
  $data["wiki"]=array();
  $data["wiki"]["home_article"]=$settings["wiki_home_article"];
  $user_record["json_data"]=json_encode($data);
  \webdb\chat\update_user($user_record);
  return $data["wiki"];
}

#####################################################################################################

function upload_file($submit_name,$target_filename)
{
  global $settings;
  if (isset($_FILES)==false)
  {
    return;
  }
  if (isset($_FILES[$submit_name])==false)
  {
    return;
  }
  $upload_data=$_FILES[$submit_name];
  $upload_filename=$upload_data["tmp_name"];
  if ($upload_filename=="")
  {
    return;
  }
  if (file_exists($upload_filename)==false)
  {
    \webdb\utils\error_message("error: uploaded file not found");
  }
  switch ($settings["file_upload_mode"])
  {
    case "rename":
      $target_filename=$settings["app_file_uploads_path"].$target_filename;
      rename($upload_filename,$target_filename);
      return;
    case "ftp":
      $target_filename=$settings["ftp_app_target_path"].$target_filename;
      $connection=\webdb\utils\webdb_ftp_login();
      ftp_put($connection,$target_filename,$upload_filename,FTP_BINARY);
      ftp_close($connection);
      return;
  }
  \webdb\utils\error_message("error: invalid file upload mode");
}

#####################################################################################################