<?php
namespace MagicObject\ComplexNumber;

class ComplexNumber {

    /**
     * Real number
     *
     * @var double
     */
    private $real;

    /**
     * Imaginary number
     *
     * @var double
     */
    private $imaginary;

    /**
     * Constructor
     *
     * @param double $real Real value
     * @param double $imaginary Imaginary value
     */
    public function __construct($real, $imaginary) {
        $this->real = $real;
        $this->imaginary = $imaginary;
    }

    /**
     * Add complex number
     *
     * @param self $self Complex number
     * @return self
     */
    public function add($self) {
        return new self(
            $this->real + $self->getReal(),
            $this->imaginary + $self->getImaginary()
        );
    }

    /**
     * Subtract complex number
     *
     * @param self $self Complex number
     * @return self
     */
    public function subtract($self) {
        return new self(
            $this->real - $self->getReal(),
            $this->imaginary - $self->getImaginary()
        );
    }

    /**
     * Multiply complex number
     *
     * @param self $self Complex number
     * @return self
     */
    public function multiply($self) {
        $real = $this->real * $self->getReal()
            - $this->imaginary * $self->getImaginary();

        $imaginary = $this->real * $self->getImaginary()
            + $this->imaginary * $self->getReal();

        return new self($real, $imaginary);
    }

    /**
     * Divide complex number
     *
     * @param self $self Complex number
     * @return self
     */
    public function divide($self) {
        $denominator = $self->getReal()**2
            + $self->getImaginary()**2;

        $real = ($this->real * $self->getReal()
            + $this->imaginary * $self->getImaginary())
            / $denominator;

        $imaginary = ($this->imaginary * $self->getReal()
            - $this->real * $self->getImaginary())
            / $denominator;

        return new self($real, $imaginary);
    }

    /**
     * Magnitude
     *
     * @return double
     */
    public function magnitude() {
        return sqrt($this->real**2 + $this->imaginary**2);
    }

    /**
     * Conjugate
     *
     * @return self
     */
    public function conjugate() {
        return new self($this->real, -$this->imaginary);
    }

    /**
     * Print comlpex number
     *
     * @return string
     */
    public function __toString() {
        return "({$this->real}, {$this->imaginary}i)";
    }

    /**
     * Get real number
     *
     * @return double
     */
    public function getReal()
    {
        return $this->real;
    }

    /**
     * Set real number
     *
     * @param double $real Real number
     *
     * @return self
     */
    public function setReal($real)
    {
        $this->real = $real;

        return $this;
    }

    /**
     * Get imaginary number
     *
     * @return double
     */
    public function getImaginary()
    {
        return $this->imaginary;
    }

    /**
     * Set imaginary number
     *
     * @param double $imaginary Imaginary number
     *
     * @return self
     */
    public function setImaginary($imaginary)
    {
        $this->imaginary = $imaginary;

        return $this;
    }
}