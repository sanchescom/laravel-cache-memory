<?php

namespace Sanchescom\Cache;

/**
 * Class Block.
 */
class MemoryBlock
{
    /**
     * For create (sets IPC_CREATE) use this flag when you need to create a new shared memory segment or if a segment
     * with the same key exists, try to open it for read and write.
     *
     * @var string
     */
    const CREATE_MODE = 'c';

    /**
     * For read & write access use this flag when you need to read and write to a shared memory segment, use this flag
     * in most cases.
     *
     * @var string
     */
    const WRITE_MODE = 'w';

    /**
     * Holds the default permission (octal) that will be used in created memory blocks.
     *
     * @var int
     * @access protected
     */
    protected $perms = 0644;

    /**
     * The size of the shared memory block you wish to create in bytes.
     *
     * @var int
     */
    protected $size = 320000;

    /**
     * Holds the system id for the shared memory block.
     *
     * @var int
     */
    protected $key;

    /**
     * Holds the shared memory block id returned by shmop_open.
     *
     * @var resource
     */
    protected $id;

    /**
     * Shared memory block instantiation.
     *
     * In the constructor we'll check if the block we're going to manipulate
     * already exists or needs to be created. If it exists, let's open it.
     *
     * @param string $key (optional) ID of the shared memory block you want to manipulate
     * @param int $size
     */
    public function __construct($key = null, $size = 320000)
    {
        $this->size = (int)$size;
        $this->key = $this->makeKey($key);
        $this->id = $this->open();
    }

    /**
     * Checks if a shared memory block with the provided id exists or not.
     *
     * In order to check for shared memory existance, we have to open it with
     * reading access. If it doesn't exist, warnings will be cast, therefore we
     * suppress those with the @ operator.
     *
     * @param string $key ID of the shared memory block you want to check
     *
     * @return boolean True if the block exists, false if it doesn't
     */
    public function isExists($key)
    {
        $status = @shmop_open($key, "a", 0, 0);

        return $status;
    }

    /**
     * Writes on a shared memory block.
     *
     * First we check for the block existance, and if it doesn't, we'll create it. Now, if the
     * block already exists, we need to delete it and create it again with a new byte allocation that
     * matches the size of the data that we want to write there. We mark for deletion,  close the semaphore
     * and create it again.
     *
     * @param string $data The data that you wan't to write into the shared memory block
     */
    public function write($data)
    {
        if (!$this->isExists($this->key)) {
            $this->id = $this->open();
        }

        shmop_write($this->id, $data, 0);
    }

    /**
     * Reads from a shared memory block.
     *
     * @return string The data read from the shared memory block
     */
    public function read()
    {
        $data = shmop_read($this->id, 0, shmop_size($this->id));

        return $data;
    }

    /**
     * Mark a shared memory block for deletion.
     */
    public function delete()
    {
        shmop_delete($this->id);
    }

    /**
     * Close a shared memory block.
     */
    public function close()
    {
        shmop_close($this->id);
    }

    /**
     * Create or open shared memory block.
     *
     * @return resource
     */
    public function open()
    {
        return shmop_open(
            $this->getKey(),
            $this->getMode(),
            $this->getPermissions(),
            $this->getSize()
        );
    }

    /**
     * Gets the current shared memory block id.
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Gets the current shared memory block permissions.
     */
    public function getPermissions()
    {
        return $this->perms;
    }

    /**
     * Sets the default permission (octal) that will be used in created memory blocks.
     *
     * @param string $perms Permissions, in octal form
     */
    public function setPermissions($perms)
    {
        $this->perms = $perms;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        if ($this->isExists($this->getKey())) {
            return static::WRITE_MODE;
        }

        return static::CREATE_MODE;
    }

    /**
     * Gets the current shared memory size.
     *
     * Note that this is the size specified by the user.
     *
     * This size can be different from the size obtained from getSizeInMemory()
     * because, e.g., the user has changed the size limit but the change is not yet communicated to the OS kernel.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Gets the current shared memory size.
     *
     * Note that this is the size of the actual memory block managed by the OS kernel.
     *
     * This size can be different from the size obtained from getSize().
     * When the specified memory size is updated by the user,
     * the OS must delete and recreate the memory block so that the new size can be applied.
     *
     * @return int
     */
    public function getSizeInMemory()
    {
        return shmop_size($this->id);
    }

    /**
     * Makes a System V IPC key from pathname and a project identifier.
     *
     * @param null $shmKey
     *
     * @return int|string
     */
    protected function makeKey($shmKey = null)
    {
        return $shmKey ?: ftok(__FILE__, 'b');
    }
}
