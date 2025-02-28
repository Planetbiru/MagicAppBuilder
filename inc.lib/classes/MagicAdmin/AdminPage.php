<?php

namespace MagicAdmin;

class AdminPage
{
    /**
     * Generates an HTML sidebar menu based on a JSON structure and the current active link.
     *
     * This function dynamically generates a sidebar in HTML format. It reads menu data from a provided
     * JSON object, and adds submenu items if available. If the `currentHref` matches any submenu item's href,
     * the respective submenu will be expanded by adding the "show" class to its `collapse` div.
     *
     * @param string $jsonData A JSON-encoded string representing the menu structure, including main items and submenus.
     * @param string $currentHref The href of the current page, used to determine which submenu (if any) should be expanded.
     * @param AppLanguage $appLanguage Application language
     * 
     * @return string The generated HTML for the sidebar, including the main menu and any expanded submenus.
     */
    public static function generateSidebar($jsonData, $currentHref, $appLanguage) // NOSONAR
    {
        // Decode JSON data
        $data = json_decode($jsonData, true);
        
        // Start the sidebar HTML structure
        $sidebarHTML = '<ul class="nav flex-column" id="sidebarMenu">';

        // Loop through each main menu item
        foreach ($data['menu'] as $item) {
            $sidebarHTML .= '<li class="nav-item">';

            $item['title'] = $appLanguage->get(strtolower(str_replace(' ', '_', $item['title'])));
            
            // Link for the main menu item, add collapse toggle if there are submenus
            $sidebarHTML .= '<a class="nav-link" href="' . $item['href'] . '"';
            
            // Add target="_blank" if specified in the JSON (or set default)
            $target = isset($item['target']) ? $item['target'] : '';
            if ($target) {
                $sidebarHTML .= ' target="' . $target . '"';
            }

            if (count($item['submenu']) > 0) {
                $sidebarHTML .= ' data-toggle="collapse" aria-expanded="false"';
            }
            $sidebarHTML .= '><i class="' . $item['icon'] . '"></i> ' . $item['title'] . '</a>'."\r\n";
            
            // Check if there are submenus
            if (count($item['submenu']) > 0) {
                // Check if currentHref matches any of the submenu items' href
                $isActive = false;
                foreach ($item['submenu'] as $subItem) {
                    if ($subItem['href'] === $currentHref) {
                        $isActive = true;
                        break;
                    }
                }
                
                // Add class "show" if the currentHref matches any submenu item
                $collapseClass = $isActive ? 'collapse show' : 'collapse';
                $sidebarHTML .= '<div id="' . substr($item['href'], 1) . '" class="' . $collapseClass . '">'."\r\n";
                $sidebarHTML .= '<ul class="nav flex-column pl-3">'."\r\n";
                
                // Loop through each submenu item
                foreach ($item['submenu'] as $subItem) {
                    $subItem['title'] = $appLanguage->get(strtolower(str_replace(' ', '_', $subItem['title'])));
                    $sidebarHTML .= '<li class="nav-item">';
                    $sidebarHTML .= '<a class="nav-link" href="' . $subItem['href'] . '"';
                    
                    // Add target="_blank" for submenu links if specified
                    $subTarget = isset($subItem['target']) ? $subItem['target'] : '';
                    if ($subTarget) {
                        $sidebarHTML .= ' target="' . $subTarget . '"';
                    }

                    $sidebarHTML .= '><i class="' . $subItem['icon'] . '"></i> ' . $subItem['title'] . '</a>';
                    $sidebarHTML .= '</li>'."\r\n";
                }
                
                $sidebarHTML .= '</ul>'."\r\n";
                $sidebarHTML .= '</div>'."\r\n";
            }

            $sidebarHTML .= '</li>'."\r\n";
        }

        // Close the sidebar HTML structure
        $sidebarHTML .= '</ul>';

        // Return the generated sidebar HTML
        return $sidebarHTML;
    }
}