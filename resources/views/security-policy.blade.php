@extends('layouts.public')

@section('title', 'Security Policy | BluStack')

@section('header-subtitle', 'BluStack')
@section('header-title', 'Security Policy')

@section('header-logo')
    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold">
        <i class="fas fa-shield-alt"></i>
    </div>
@endsection

@section('auth-button-text', 'Dashboard')

@section('container-class', 'max-w-5xl')

@section('content')
<div class="bg-slate-900/50 backdrop-blur-sm border border-slate-800 rounded-2xl p-8 lg:p-12 shadow-2xl">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-white mb-4">Security Policy for Syncing Orders from TikTok Shops</h2>
        <p class="text-slate-400 text-lg">
            BluStack is committed to maintaining the highest standards of security when synchronizing order data from TikTok Shops. This policy outlines our comprehensive security measures and best practices.
        </p>
    </div>

    <div class="space-y-8">
        <!-- 1. Authentication and Authorization -->
        <section class="bg-slate-800/50 rounded-xl p-6 border border-slate-700">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-blue-600/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-key text-blue-400"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-white mb-2">1. Authentication and Authorization</h3>
                    <ul class="space-y-3 text-slate-300">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">OAuth Integration:</strong> BluStack uses OAuth 2.0 for secure communication between TikTok and our application. We ensure proper handling of access tokens and refresh tokens to maintain continuous secure access.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">API Key Management:</strong> BluStack requires TikTok shops to use unique API keys for authentication. We implement periodic API key rotation to maintain security and prevent unauthorized access.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">User Roles:</strong> BluStack implements role-based access control (RBAC) within our application, ensuring only authorized users can initiate syncs or access synchronized data.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">Session Management:</strong> BluStack ensures sessions are secure, utilizing HTTPS and enforcing expiration policies. We use secure tokens with short expiration periods to minimize security risks.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- 2. Data Encryption -->
        <section class="bg-slate-800/50 rounded-xl p-6 border border-slate-700">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-purple-600/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-lock text-purple-400"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-white mb-2">2. Data Encryption</h3>
                    <ul class="space-y-3 text-slate-300">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">In Transit:</strong> All data exchanged between BluStack and TikTok shops is encrypted using TLS (Transport Layer Security) to protect data during transmission.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">At Rest:</strong> BluStack stores synchronized order data securely in our database using AES-256 encryption to protect sensitive information.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">Access Control:</strong> BluStack limits access to decrypted data based on roles and permissions, ensuring only authorized personnel can view sensitive information.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- 3. Data Integrity -->
        <section class="bg-slate-800/50 rounded-xl p-6 border border-slate-700">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-green-600/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shield-alt text-green-400"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-white mb-2">3. Data Integrity</h3>
                    <ul class="space-y-3 text-slate-300">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">Checksum Validation:</strong> BluStack implements mechanisms to verify the integrity of incoming data using hash checks and signatures to ensure data has not been tampered with.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">Transaction Logs:</strong> BluStack logs all data sync activities (time, user, source shop, etc.) to create a comprehensive auditable trail for security monitoring and compliance.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- 4. Rate Limiting and Throttling -->
        <section class="bg-slate-800/50 rounded-xl p-6 border border-slate-700">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-yellow-600/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-tachometer-alt text-yellow-400"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-white mb-2">4. Rate Limiting and Throttling</h3>
                    <ul class="space-y-3 text-slate-300">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">API Rate Limits:</strong> BluStack enforces rate limits on API requests to prevent abuse, both on our application and in the TikTok API, ensuring system stability.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">Throttling:</strong> BluStack implements throttling mechanisms to prevent accidental denial-of-service (DoS) due to high-volume sync requests, maintaining optimal performance.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- 5. Error Handling and Monitoring -->
        <section class="bg-slate-800/50 rounded-xl p-6 border border-slate-700">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-red-600/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-white mb-2">5. Error Handling and Monitoring</h3>
                    <ul class="space-y-3 text-slate-300">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">Error Logging:</strong> BluStack logs all errors related to syncing orders for quick identification and resolution, enabling proactive security management.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">Alerting:</strong> BluStack sets up real-time alerting for critical errors or anomalies during the sync process (e.g., authentication failures, data mismatches).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">Fallback Mechanisms:</strong> BluStack implements retry logic and fallback strategies in case of TikTok API failures or connectivity issues, ensuring service continuity.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- 6. Data Privacy and Compliance -->
        <section class="bg-slate-800/50 rounded-xl p-6 border border-slate-700">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-indigo-600/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user-shield text-indigo-400"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-white mb-2">6. Data Privacy and Compliance</h3>
                    <ul class="space-y-3 text-slate-300">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">GDPR Compliance:</strong> BluStack ensures the synchronization process complies with data protection regulations (GDPR, CCPA) when dealing with customer information.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">Data Minimization:</strong> BluStack syncs only necessary order information and avoids storing unnecessary personal information, adhering to privacy best practices.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">Consent Management:</strong> BluStack obtains explicit consent from shop owners for syncing their data to our application, ensuring transparency and compliance.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- 7. Security Testing and Audits -->
        <section class="bg-slate-800/50 rounded-xl p-6 border border-slate-700">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-teal-600/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-search text-teal-400"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-white mb-2">7. Security Testing and Audits</h3>
                    <ul class="space-y-3 text-slate-300">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">Regular Security Audits:</strong> BluStack conducts regular audits and penetration testing to identify vulnerabilities in the sync process, maintaining a proactive security posture.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">Vulnerability Management:</strong> BluStack keeps our application up-to-date with security patches, especially libraries or APIs related to TikTok integration, ensuring protection against known vulnerabilities.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">Continuous Monitoring:</strong> BluStack sets up continuous monitoring for potential security breaches during order synchronization, enabling rapid detection and response.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- 8. Incident Response -->
        <section class="bg-slate-800/50 rounded-xl p-6 border border-slate-700">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-orange-600/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-ambulance text-orange-400"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-white mb-2">8. Incident Response</h3>
                    <ul class="space-y-3 text-slate-300">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">Breach Notification:</strong> BluStack has a clear process in place to notify affected parties in case of a data breach or unauthorized access, ensuring transparency and compliance with regulations.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-400 mt-1 flex-shrink-0"></i>
                            <span><strong class="text-white">Recovery Mechanism:</strong> BluStack plans for disaster recovery and data restoration in case of a critical security incident, ensuring business continuity and data protection.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer Note -->
    <div class="mt-10 pt-8 border-t border-slate-700">
        <div class="bg-blue-600/10 border border-blue-500/20 rounded-lg p-6">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-400 mt-1"></i>
                <div>
                    <h4 class="text-sm font-semibold text-blue-400 mb-2">Commitment to Security</h4>
                    <p class="text-sm text-slate-300">
                        BluStack is committed to maintaining the highest standards of security. This policy is regularly reviewed and updated to reflect the latest security best practices and regulatory requirements. If you have any questions or concerns about our security practices, please contact our security team.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-8 text-center">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-medium transition-colors">
            <i class="fas fa-arrow-left"></i>
            Back to Home
        </a>
    </div>
</div>
@endsection
