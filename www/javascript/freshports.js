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
 * freshports.css.  The Menu links keep href="#sidebar", so without this
 * script they still jump to the sidebar in the stacked layout.
 */
(function () {
   var root    = document.documentElement;
   var sidebar = document.querySelector('td.sidebar');
   var openers = document.querySelectorAll('.menu-link, .menu-button');

   if (!sidebar || !openers.length || !window.matchMedia) {
      return;
   }

   // must match the max-width of the narrow-screen rules in freshports.css
   var narrow   = window.matchMedia('(max-width: 1120px)');
   var returnTo = null;

   sidebar.id = 'sidebar-drawer';

   var closeButton = document.createElement('button');
   closeButton.type        = 'button';
   closeButton.className   = 'drawer-close';
   closeButton.textContent = 'Close ✕';
   sidebar.insertBefore(closeButton, sidebar.firstChild);

   var backdrop = document.createElement('div');
   backdrop.className = 'drawer-backdrop';
   document.body.appendChild(backdrop);

   function setExpanded(expanded) {
      for (var i = 0; i < openers.length; i++) {
         openers[i].setAttribute('aria-expanded', expanded ? 'true' : 'false');
      }
   }

   function openDrawer(opener) {
      returnTo = opener;
      root.classList.add('drawer-open');
      setExpanded(true);
      closeButton.focus();
   }

   function closeDrawer() {
      if (!root.classList.contains('drawer-open')) {
         return;
      }
      root.classList.remove('drawer-open');
      setExpanded(false);
      if (returnTo) {
         returnTo.focus();
      }
   }

   for (var i = 0; i < openers.length; i++) {
      openers[i].setAttribute('aria-controls', sidebar.id);
      openers[i].addEventListener('click', function (event) {
         if (!narrow.matches) {
            return;
         }
         event.preventDefault();
         openDrawer(this);
      });
   }

   closeButton.addEventListener('click', closeDrawer);
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

   setExpanded(false);
   root.classList.add('has-drawer');
})();
