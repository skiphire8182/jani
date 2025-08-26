import { serve } from "https://deno.land/std@0.168.0/http/server.ts"
import { createClient } from 'https://esm.sh/@supabase/supabase-js@2'

const corsHeaders = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
}

serve(async (req) => {
  if (req.method === 'OPTIONS') {
    return new Response('ok', { headers: corsHeaders })
  }

  try {
    const supabaseClient = createClient(
      Deno.env.get('SUPABASE_URL') ?? '',
      Deno.env.get('SUPABASE_ANON_KEY') ?? '',
    )

    const { action, ...data } = await req.json()

    switch (action) {
      case 'login':
        const { email, password } = data
        const { data: authData, error: authError } = await supabaseClient.auth.signInWithPassword({
          email,
          password,
        })

        if (authError) {
          return new Response(
            JSON.stringify({ success: false, message: authError.message }),
            { headers: { ...corsHeaders, 'Content-Type': 'application/json' }, status: 400 }
          )
        }

        // Get user profile from users table
        const { data: userData, error: userError } = await supabaseClient
          .from('users')
          .select('*')
          .eq('email', email)
          .single()

        if (userError) {
          return new Response(
            JSON.stringify({ success: false, message: 'User profile not found' }),
            { headers: { ...corsHeaders, 'Content-Type': 'application/json' }, status: 404 }
          )
        }

        return new Response(
          JSON.stringify({
            success: true,
            user: userData,
            session: authData.session
          }),
          { headers: { ...corsHeaders, 'Content-Type': 'application/json' } }
        )

      case 'register':
        const { email: regEmail, password: regPassword, full_name, role = 'user' } = data
        
        // Create auth user
        const { data: regAuthData, error: regAuthError } = await supabaseClient.auth.signUp({
          email: regEmail,
          password: regPassword,
        })

        if (regAuthError) {
          return new Response(
            JSON.stringify({ success: false, message: regAuthError.message }),
            { headers: { ...corsHeaders, 'Content-Type': 'application/json' }, status: 400 }
          )
        }

        // Create user profile
        const { error: profileError } = await supabaseClient
          .from('users')
          .insert({
            user_id: regAuthData.user?.id,
            username: regEmail.split('@')[0],
            email: regEmail,
            full_name,
            role,
            is_active: true
          })

        if (profileError) {
          return new Response(
            JSON.stringify({ success: false, message: profileError.message }),
            { headers: { ...corsHeaders, 'Content-Type': 'application/json' }, status: 400 }
          )
        }

        return new Response(
          JSON.stringify({ success: true, message: 'User registered successfully' }),
          { headers: { ...corsHeaders, 'Content-Type': 'application/json' } }
        )

      default:
        return new Response(
          JSON.stringify({ success: false, message: 'Invalid action' }),
          { headers: { ...corsHeaders, 'Content-Type': 'application/json' }, status: 400 }
        )
    }
  } catch (error) {
    return new Response(
      JSON.stringify({ success: false, message: error.message }),
      { headers: { ...corsHeaders, 'Content-Type': 'application/json' }, status: 500 }
    )
  }
})