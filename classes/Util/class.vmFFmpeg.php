<?php
require_once('./Services/MediaObjects/classes/class.ilFFmpeg.php');
/**
 * Class vmFFmpeg
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class vmFFmpeg extends \ilFFmpeg{

    /**
     * Formats handled by ILIAS. Note: In general the mime types
     * do not reflect the complexity of media container/codec variants.
     * For source formats no specification is needed here. For target formats
     * we use fixed parameters that should result in best web media practice.
     */
    static $formats = array(
        "video/3pgg" => array(
            "source" => true,
            "target" => false
        ),
        "video/x-flv" => array(
            "source" => true,
            "target" => false
        ),
        "video/mp4" => array(
            "source" => true,
            "target" => true,
            "parameters" => "-strict -2",
            "suffix" => "mp4"
        ),
        "video/webm" => array(
            "source" => true,
            "target" => true,
            "parameters" => "-c:v libvpx -crf 10 -b:v 1M -c:a libvorbis",
            "suffix" => "webm"
        )
    );

    /**
     * Convert file to target mime type
     *
     * @param string $a_file source file (full path included)
     * @param string $a_target_mime target mime type
     * @param string $a_target_dir target directory (no trailing "/")
     * @param string $a_target_filename target file name (no path!)
     *
     * @return string new file (full path)
     */
    static function convert($a_file, $a_target_mime, $a_target_dir = "", $a_target_filename = "")
    {

        if (self::$formats[$a_target_mime]["target"] != true)
        {
            include_once("./Services/MediaObjects/exceptions/class.ilFFmpegException.php");
            throw new ilFFmpegException("Format ".$a_target_mime." is not supported");
        }
        $pars = self::$formats[$a_target_mime]["parameters"];
        $spi = pathinfo($a_file);

        // use source directory if no target directory is passed
        $target_dir = ($a_target_dir != "")
            ? $a_target_dir
            : $spi['dirname'];

        // use source filename if no target filename is passed
        $target_filename = ($a_target_filename != "")
            ? $a_target_filename
            : $spi['filename'].".".self::$formats[$a_target_mime]["suffix"];

        $target_file = $target_dir."/".$target_filename;

        $cmd = "-y -i ".ilUtil::escapeShellArg($a_file)." ".$pars." ".ilUtil::escapeShellArg($target_file);

        $ret = self::exec($cmd." 2>&1");
        self::$last_return = $ret;

        if (is_file($target_file))
        {
            return $target_file;
        }
        else
        {
            include_once("./Services/MediaObjects/exceptions/class.ilFFmpegException.php");
            throw new ilFFmpegException("It was not possible to convert file ".basename($a_file).".");
        }
    }

    /**
     * @param $file
     * @return int duration in seconds
     */
    static function getDuration($file)
    {

        //$time = 00:00:00.000 format
        $cmd = "-i ".ilUtil::escapeShellArg($file)." 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//";
        $time =  self::exec($cmd);

        $duration = explode(":",$time[0]);
        $duration_in_seconds = $duration[0]*3600 + $duration[1]*60+ round($duration[2]);

        return $duration_in_seconds;
    }

} 