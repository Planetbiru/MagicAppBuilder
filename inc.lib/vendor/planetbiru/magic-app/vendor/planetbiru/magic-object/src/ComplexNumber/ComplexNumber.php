<?php

namespace MagicObject\ComplexNumber;

class ComplexNumber {

    /**
     * Real part of the complex number.
     *
     * @var float
     */
    private $real;

    /**
     * Imaginary part of the complex number.
     *
     * @var float
     */
    private $imaginary;

    /**
     * Constructor to initialize the complex number.
     *
     * @param float $real Real part of the complex number.
     * @param float $imaginary Imaginary part of the complex number.
     */
    public function __construct($real, $imaginary) {
        $this->real = $real;
        $this->imaginary = $imaginary;
    }

    /**
     * Add another complex number to this one.
     *
     * @param self $self The complex number to add.
     * @return self The sum of the two complex numbers.
     */
    public function add($self) {
        return new self(
            $this->real + $self->getReal(),
            $this->imaginary + $self->getImaginary()
        );
    }

    /**
     * Subtract another complex number from this one.
     *
     * @param self $self The complex number to subtract.
     * @return self The result of the subtraction.
     */
    public function subtract($self) {
        return new self(
            $this->real - $self->getReal(),
            $this->imaginary - $self->getImaginary()
        );
    }

    /**
     * Multiply this complex number by another.
     *
     * @param self $self The complex number to multiply.
     * @return self The product of the two complex numbers.
     */
    public function multiply($self) {
        $real = $this->real * $self->getReal()
            - $this->imaginary * $self->getImaginary();

        $imaginary = $this->real * $self->getImaginary()
            + $this->imaginary * $self->getReal();

        return new self($real, $imaginary);
    }

    /**
     * Divide this complex number by another.
     *
     * @param self $self The complex number to divide by.
     * @return self The result of the division.
     */
    public function divide($self) {
        $denominator = $self->getReal() ** 2
            + $self->getImaginary() ** 2;

        $real = ($this->real * $self->getReal()
            + $this->imaginary * $self->getImaginary())
            / $denominator;

        $imaginary = ($this->imaginary * $self->getReal()
            - $this->real * $self->getImaginary())
            / $denominator;

        return new self($real, $imaginary);
    }

    /**
     * Get the magnitude of the complex number.
     *
     * @return float The magnitude of the complex number.
     */
    public function magnitude() {
        return sqrt($this->real ** 2 + $this->imaginary ** 2);
    }

    /**
     * Get the conjugate of the complex number.
     *
     * @return self The conjugate of the complex number.
     */
    public function conjugate() {
        return new self($this->real, -$this->imaginary);
    }

    /**
     * String representation of the complex number.
     *
     * @return string The complex number as a string.
     */
    public function __toString() {
        return "({$this->real}, {$this->imaginary}i)";
    }

    /**
     * Get the real part of the complex number.
     *
     * @return float The real part.
     */
    public function getReal() {
        return $this->real;
    }

    /**
     * Set the real part of the complex number.
     *
     * @param float $real The real part.
     * @return self Returns the current instance for method chaining.
     */
    public function setReal($real) {
        $this->real = $real;

        return $this;
    }

    /**
     * Get the imaginary part of the complex number.
     *
     * @return float The imaginary part.
     */
    public function getImaginary() {
        return $this->imaginary;
    }

    /**
     * Set the imaginary part of the complex number.
     *
     * @param float $imaginary The imaginary part.
     * @return self Returns the current instance for method chaining.
     */
    public function setImaginary($imaginary) {
        $this->imaginary = $imaginary;

        return $this;
    }
}
