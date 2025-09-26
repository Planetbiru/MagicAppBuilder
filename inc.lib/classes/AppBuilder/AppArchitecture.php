<?php

namespace AppBuilder;

/**
 * Class AppArchitecture
 *
 * Defines the available application architectures within the system.
 *
 * The application architectures include:
 * - Monolith: An architecture that consolidates all components into a single unit.
 * - Microservices: An architecture that consists of many small, independent services.
 *
 * @package AppBuilder
 */
class AppArchitecture
{
    const MONOLITH      = "monolith";      // Monolith application architecture
    const MICROSERVICES = "microservices"; // Microservices application architecture
}
