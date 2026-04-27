#!/bin/bash
# 1. Remove translate-x-full from includes/site-header.php
sed -i 's/translate-x-full //g' includes/site-header.php

# 2. Clean up conflicting Tailwind visibility classes that force hidden on mobile menu overlay
# Actually, the user wants us to standardise the state contract to .active.
# Let's inspect the files and do exactly what is requested.
