/* example from http://www.cssnewbie.com/example/showhide-content/ */

function showHide(shID) {
   if (document.getElementById(shID)) {
      if (document.getElementById(shID+'-show').style.display != 'none') {
         document.getElementById(shID+'-show').style.display = 'none';
         document.getElementById(shID).style.display = 'block';
      }
      else {
         document.getElementById(shID+'-show').style.display = 'inline';
         document.getElementById(shID).style.display = 'none';
      }
   }
}

/*
 * On narrow screens, the sidebar flies in from the right instead of sitting
 * below the page content - see issue #636 and the matching rules in
 * freshports.css.  The Menu button, pinned to the top-right corner, opens
 * the drawer and becomes its Close button, so the menu can be toggled
 * without moving your hand.  The Menu links keep href="#sidebar", so
 * without this script they still jump to the sidebar in the stacked layout.
 */
(function () {
   var root    = document.documentElement;
   var sidebar = document.querySelector('td.sidebar');
   var button  = document.querySelector('.menu-button');
   var link    = document.querySelector('.menu-link');

   if (!sidebar || !button || !window.matchMedia) {
      return;
   }

   // must match the max-width of the narrow-screen rules in freshports.css
   var narrow = window.matchMedia('(max-width: 1120px)');

   var menuLabel  = button.innerHTML;
   var menuTitle  = button.getAttribute('title');
   // the page supplies these in the visitor's language - see freshports_Logo()
   var closeLabel = '\u2715 ' + (button.getAttribute('data-close-label') || 'Close');
   var closeTitle = button.getAttribute('data-close-title') || 'Close the menu';

   sidebar.id = 'sidebar-drawer';
   button.setAttribute('role', 'button');
   button.setAttribute('aria-controls', sidebar.id);

   var backdrop = document.createElement('div');
   backdrop.className = 'drawer-backdrop';
   document.body.appendChild(backdrop);

   function isOpen() {
      return root.classList.contains('drawer-open');
   }

   function openDrawer() {
      root.classList.add('drawer-open');
      button.setAttribute('aria-expanded', 'true');
      button.textContent = closeLabel;
      button.setAttribute('title', closeTitle);
      button.focus();
   }

   function closeDrawer() {
      if (!isOpen()) {
         return;
      }
      var focusWasInside = sidebar.contains(document.activeElement);
      root.classList.remove('drawer-open');
      button.setAttribute('aria-expanded', 'false');
      button.innerHTML = menuLabel;
      button.setAttribute('title', menuTitle);
      if (focusWasInside) {
         button.focus();
      }
   }

   button.addEventListener('click', function (event) {
      if (!narrow.matches) {
         return;
      }
      event.preventDefault();
      if (isOpen()) {
         closeDrawer();
      } else {
         openDrawer();
      }
   });

   if (link) {
      link.addEventListener('click', function (event) {
         if (!narrow.matches) {
            return;
         }
         event.preventDefault();
         openDrawer();
      });
   }

   backdrop.addEventListener('click', closeDrawer);

   document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
         closeDrawer();
      }
   });

   // e.g. an iPad rotated to landscape: the sidebar goes back beside the content
   var onChange = function () {
      if (!narrow.matches) {
         closeDrawer();
      }
   };
   if (narrow.addEventListener) {
      narrow.addEventListener('change', onChange);
   } else {
      narrow.addListener(onChange);
   }
   // not every browser fires the change event above on rotation
   window.addEventListener('resize', onChange);

   button.setAttribute('aria-expanded', 'false');
   root.classList.add('has-drawer');
})();
