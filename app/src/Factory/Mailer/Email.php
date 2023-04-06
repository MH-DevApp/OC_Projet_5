<?php

/**
 * Email file
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */

declare(strict_types=1);

namespace App\Factory\Mailer;


/**
 * Email class
 *
 * Email object for send to Mailer.
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class Email
{

    private string $from = "contact@p5-daps-oc.fr";
    private string $to = "contact@p5-daps-oc.fr";
    private string $subject = "";
    private string $body = "";


    /**
     * Get Email from.
     * Default value : "contact@p5-daps-oc.fr"
     *
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;

    }


    /**
     * Set Email from.
     *
     * @param string $from
     *
     * @return $this
     */
    public function setFrom(string $from): self
    {
        $this->from = $from;
        return $this;

    }


    /**
     * Get Email to.
     * Default value : "contact@p5-daps-oc.fr"
     *
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;

    }


    /**
     * Set Email to.
     *
     * @param string $to
     *
     * @return $this
     */
    public function setTo(string $to): self
    {
        $this->to = $to;
        return $this;

    }


    /**
     * Get Email subject.
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;

    }


    /**
     * Set Email subject.
     *
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;

    }


    /**
     * Get Email body.
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;

    }


    /**
     * Set Email body.
     *
     * @param string $body
     *
     * @return $this
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;

    }


}
