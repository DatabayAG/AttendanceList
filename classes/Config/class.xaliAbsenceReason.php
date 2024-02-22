<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);


class xaliAbsenceReason extends ActiveRecord
{
    public const F_ABSENCE_REASONS_TITLE = 'title';
    public const F_ABSENCE_REASONS_INFO = 'info';
    public const F_ABSENCE_REASONS_HAS_COMMENT = 'has_comment';
    public const F_ABSENCE_REASONS_COMMENT_REQ = 'comment_req';
    public const F_ABSENCE_REASONS_HAS_UPLOAD = 'has_upload';
    public const F_ABSENCE_REASONS_UPLOAD_REQ = 'upload_req';
    public const TABLE_NAME = 'xali_absence_reasons';

    public static function returnDbTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_is_primary       true
     * @con_sequence        true
     */
    protected ?string $id = "";
    /**
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           256
     */
    protected string $title = "";
    /**
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           256
     */
    protected string $info = "";
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $has_comment = false;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $comment_req = false;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $has_upload = false;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $upload_req = false;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getInfo(): string
    {
        return $this->info;
    }

    public function setInfo(string $info): void
    {
        $this->info = $info;
    }

    public function hasComment(): bool
    {
        return $this->has_comment;
    }

    public function setHasComment(bool $has_comment): void
    {
        $this->has_comment = $has_comment;
    }

    public function hasUpload(): bool
    {
        return $this->has_upload;
    }

    public function setHasUpload(bool $has_upload): void
    {
        $this->has_upload = $has_upload;
    }

    public function getCommentReq(): bool
    {
        return $this->comment_req;
    }

    public function setCommentReq(bool $comment_req): void
    {
        $this->comment_req = $comment_req;
    }

    public function getUploadReq(): bool
    {
        return $this->upload_req;
    }

    public function setUploadReq(bool $upload_req): void
    {
        $this->upload_req = $upload_req;
    }
}
